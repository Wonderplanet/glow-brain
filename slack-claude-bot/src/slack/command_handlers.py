"""Slack slash command handlers."""

import asyncio
import re

import structlog
from slack_sdk import WebClient
from slack_sdk.errors import SlackApiError

from ..config import get_available_agents
from ..worktree.manager import WorktreeManager
from .session_handler import SessionHandler
from .views import build_agent_select_message, build_branch_select_message

logger = structlog.get_logger()

# Store pending sessions waiting for thread messages (before first prompt)
# {message_ts: {
#     "branch": str,
#     "channel_id": str,
#     "user_id": str,
#     "user_name": str,
#     "agent_name": str | None,
#     "initial_prompt": str | None,     # メンション経由の元プロンプト
#     "mention_event_ts": str | None,   # メンション経由の元ts（リアクション用）
#     "source": str,                     # "command" | "mention"
# }}
_pending_sessions = {}

# Store active sessions after first prompt (for session lookup)
# {thread_ts: {"branch": str, "channel_id": str, "user_id": str, "user_name": str, "slack_thread_id": str, "agent_name": str | None}}
_active_sessions = {}


class CommandHandlers:
    """Handle Slack slash commands and interactive components."""

    def __init__(
        self,
        session_handler: SessionHandler,
        worktree_manager: WorktreeManager,
    ):
        """Initialize command handlers.

        Args:
            session_handler: Common session handler
            worktree_manager: Worktree manager (for getting available versions)
        """
        self.session_handler = session_handler
        self.worktree_manager = worktree_manager
        self._bot_user_id = None  # Cache for bot user ID

    async def handle_glow_brain_command(
        self,
        ack,
        body: dict,
        client: WebClient,
    ) -> None:
        """Handle /mst-input-guide command - post branch selection message.

        Args:
            ack: Acknowledge function
            body: Slack command payload
            client: Slack WebClient
        """
        await ack()

        try:
            # Get available versions from config/versions.json
            versions = self.worktree_manager.get_available_versions()

            logger.info(
                "glow_brain_command_received",
                user_id=body["user_id"],
                channel_id=body["channel_id"],
                versions_count=len(versions),
            )

            # Get current version
            current_version = versions[0] if versions else None

            # Build branch selection message
            message = build_branch_select_message(
                versions=versions,
                current_version=current_version,
            )

            # Post message to channel
            await client.chat_postMessage(
                channel=body["channel_id"],
                **message,
            )

            logger.info(
                "branch_select_message_posted",
                channel_id=body["channel_id"],
                user_id=body["user_id"],
            )

        except FileNotFoundError as e:
            logger.error(
                "versions_file_not_found",
                error=str(e),
            )
            await client.chat_postEphemeral(
                channel=body["channel_id"],
                user=body["user_id"],
                text=f"エラー: versions.jsonが見つかりませんでした: {str(e)}",
            )

        except Exception as e:
            logger.error(
                "glow_brain_command_failed",
                error=str(e),
            )
            await client.chat_postEphemeral(
                channel=body["channel_id"],
                user=body["user_id"],
                text=f"エラーが発生しました: {str(e)}",
            )

    async def handle_branch_select_action(
        self,
        ack,
        body: dict,
        client: WebClient,
    ) -> None:
        """Handle branch selection button click - show agent selection.

        Args:
            ack: Acknowledge function
            body: Slack action payload
            client: Slack WebClient
        """
        await ack()

        try:
            action = body["actions"][0]
            branch = action["value"]
            user_id = body["user"]["id"]
            user_name = body["user"]["name"]
            channel_id = body["channel"]["id"]
            message_ts = body["message"]["ts"]

            # スレッド内のボタンクリックの場合、thread_ts を取得
            # メンション経由の場合は thread_ts が親メンションのtsになる
            thread_ts = body["message"].get("thread_ts") or message_ts

            logger.info(
                "branch_selected",
                branch=branch,
                user_id=user_id,
                channel_id=channel_id,
                thread_ts=thread_ts,
            )

            # 既存の pending_session があれば更新、なければ新規作成
            if thread_ts in _pending_sessions:
                # メンション経由（既にセッション情報がある）
                _pending_sessions[thread_ts]["branch"] = branch
                session_info = _pending_sessions[thread_ts]
            else:
                # コマンド経由（新規作成）
                _pending_sessions[thread_ts] = {
                    "branch": branch,
                    "channel_id": channel_id,
                    "user_id": user_id,
                    "user_name": user_name,
                    "agent_name": None,
                    "initial_prompt": None,
                    "mention_event_ts": None,
                    "source": "command",
                }
                session_info = _pending_sessions[thread_ts]

            # Get available agents
            agents = get_available_agents()

            if agents:
                # Show agent selection
                message = build_agent_select_message(agents, branch)
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    **message,
                )
            else:
                # No agents available
                if session_info.get("initial_prompt"):
                    # メンション経由で initial_prompt がある場合は自動実行
                    await self._auto_execute_with_prompt(
                        client=client,
                        channel_id=channel_id,
                        thread_ts=thread_ts,
                        session_info=session_info,
                    )
                else:
                    # コマンド経由またはプロンプトなし
                    await client.chat_postMessage(
                        channel=channel_id,
                        thread_ts=thread_ts,
                        text=f"ブランチ `{branch}` を選択しました。\nこのスレッドにプロンプトを送信してください。",
                    )

        except Exception as e:
            logger.error("branch_select_action_failed", error=str(e), exc_info=True)

    async def handle_agent_select_action(
        self,
        ack,
        body: dict,
        client: WebClient,
    ) -> None:
        """Handle agent selection button click.

        Args:
            ack: Acknowledge function
            body: Slack action payload
            client: Slack WebClient
        """
        await ack()

        try:
            action = body["actions"][0]
            agent_name = action["value"]
            channel_id = body["channel"]["id"]
            message_ts = body["message"]["thread_ts"]  # Parent message ts

            if message_ts not in _pending_sessions:
                logger.warning("pending_session_not_found", message_ts=message_ts)
                return

            session_info = _pending_sessions[message_ts]

            # Update agent_name
            if agent_name == "__none__":
                session_info["agent_name"] = None
                agent_display = "通常モード"
            else:
                session_info["agent_name"] = agent_name
                agents = get_available_agents()
                agent_display = next(
                    (a["display_name"] for a in agents if a["name"] == agent_name),
                    agent_name
                )

            branch = session_info["branch"]
            initial_prompt = session_info.get("initial_prompt")

            logger.info(
                "agent_selected",
                agent_name=agent_name,
                branch=branch,
                channel_id=channel_id,
                has_initial_prompt=initial_prompt is not None,
            )

            # initial_prompt がある場合は自動実行
            if initial_prompt:
                await self._auto_execute_with_prompt(
                    client=client,
                    channel_id=channel_id,
                    thread_ts=message_ts,
                    session_info=session_info,
                )
            else:
                # 従来通り「プロンプトを送信してください」メッセージ
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=message_ts,
                    text=f"設定完了: ブランチ `{branch}` / エージェント `{agent_display}`\nこのスレッドにプロンプトを送信してください。",
                )

        except Exception as e:
            logger.error("agent_select_action_failed", error=str(e), exc_info=True)

    async def _get_bot_user_id(self, client: WebClient) -> str:
        """Get bot user ID (cached).

        Args:
            client: Slack WebClient

        Returns:
            Bot user ID
        """
        if self._bot_user_id is None:
            result = await client.auth_test()
            self._bot_user_id = result["user_id"]
            logger.info("bot_user_id_cached", bot_user_id=self._bot_user_id)
        return self._bot_user_id

    async def handle_thread_message(
        self,
        event: dict,
        client: WebClient,
        say,
    ) -> None:
        """Handle thread message as prompt.

        Args:
            event: Slack event data
            client: Slack WebClient
            say: Function to send messages
        """
        try:
            thread_ts = event.get("thread_ts")
            if not thread_ts:
                return

            # Ignore bot's own messages
            if event.get("bot_id"):
                return

            channel_id = event["channel"]
            user_id = event["user"]
            prompt = event["text"]

            # Check if bot is mentioned (required for command threads)
            bot_user_id = await self._get_bot_user_id(client)
            if f"<@{bot_user_id}>" not in prompt:
                return  # Ignore messages without mention

            # Remove bot mention from prompt
            prompt = re.sub(r'<@[UW][A-Z0-9]+>', '', prompt).strip()

            # Add processing reaction
            try:
                await client.reactions_add(
                    channel=channel_id,
                    timestamp=event["ts"],
                    name="hourglass_flowing_sand",
                )
            except SlackApiError as e:
                if e.response.get("error") != "already_reacted":
                    raise

            # Case 1: First message (in _pending_sessions)
            if thread_ts in _pending_sessions:
                session_info = _pending_sessions.pop(thread_ts)
                branch = session_info["branch"]
                user_name = session_info["user_name"]
                agent_name = session_info.get("agent_name")

                # Create slack_thread_id based on thread_ts
                slack_thread_id = f"cmd:{channel_id}:{thread_ts}"

                # Store as active session immediately after pop to prevent race condition
                _active_sessions[thread_ts] = {
                    "branch": branch,
                    "channel_id": channel_id,
                    "user_id": user_id,
                    "user_name": user_name,
                    "slack_thread_id": slack_thread_id,
                    "agent_name": agent_name,
                }

                logger.info(
                    "thread_prompt_received_first",
                    branch=branch,
                    channel_id=channel_id,
                    thread_ts=thread_ts,
                    slack_thread_id=slack_thread_id,
                    user_id=user_id,
                    prompt_length=len(prompt),
                )

                # Get channel info (for process_prompt)
                try:
                    channel_info = await client.conversations_info(channel=channel_id)
                    channel_name = channel_info["channel"].get("name", "command")
                except Exception:
                    channel_name = "command"

                # Get user name if not available
                if not user_name:
                    try:
                        user_info = await client.users_info(user=user_id)
                        user_name = user_info["user"].get("name", "unknown")
                    except Exception:
                        user_name = "unknown"

                # Process prompt using common handler
                await self.session_handler.process_prompt(
                    client=client,
                    channel_id=channel_id,
                    thread_ts=thread_ts,
                    slack_thread_id=slack_thread_id,
                    user_id=user_id,
                    user_name=user_name,
                    channel_name=channel_name,
                    prompt=prompt,
                    branch=branch,
                    agent_name=agent_name,
                )

            # Case 2: Continuation message (in _active_sessions)
            elif thread_ts in _active_sessions:
                session_info = _active_sessions[thread_ts]
                slack_thread_id = session_info["slack_thread_id"]
                branch = session_info["branch"]
                user_name = session_info["user_name"]
                agent_name = session_info.get("agent_name")

                logger.info(
                    "thread_prompt_received_continuation",
                    branch=branch,
                    channel_id=channel_id,
                    thread_ts=thread_ts,
                    slack_thread_id=slack_thread_id,
                    user_id=user_id,
                    prompt_length=len(prompt),
                )

                # Get channel info
                try:
                    channel_info = await client.conversations_info(channel=channel_id)
                    channel_name = channel_info["channel"].get("name", "command")
                except Exception:
                    channel_name = "command"

                # Process prompt using common handler (will resume existing session)
                await self.session_handler.process_prompt(
                    client=client,
                    channel_id=channel_id,
                    thread_ts=thread_ts,
                    slack_thread_id=slack_thread_id,
                    user_id=user_id,
                    user_name=user_name,
                    channel_name=channel_name,
                    prompt=prompt,
                    branch=branch,
                    agent_name=agent_name,
                )

            else:
                # This thread is not managed by command handlers
                return

            # Replace with success reaction
            await client.reactions_remove(
                channel=channel_id,
                timestamp=event["ts"],
                name="hourglass_flowing_sand",
            )
            try:
                await client.reactions_add(
                    channel=channel_id,
                    timestamp=event["ts"],
                    name="white_check_mark",
                )
            except SlackApiError as e:
                if e.response.get("error") != "already_reacted":
                    raise

        except Exception as e:
            logger.error("thread_message_failed", error=str(e), exc_info=True)
            # Add error reaction
            try:
                await client.reactions_add(
                    channel=event["channel"],
                    timestamp=event["ts"],
                    name="x",
                )
            except Exception:
                pass

    async def _auto_execute_with_prompt(
        self,
        client: WebClient,
        channel_id: str,
        thread_ts: str,
        session_info: dict,
    ) -> None:
        """UI選択完了後、保存されたプロンプトで自動実行.

        Args:
            client: Slack WebClient
            channel_id: Channel ID
            thread_ts: Thread timestamp (parent message ts)
            session_info: Session information from _pending_sessions
        """
        branch = session_info["branch"]
        user_id = session_info["user_id"]
        user_name = session_info["user_name"]
        agent_name = session_info.get("agent_name")
        initial_prompt = session_info["initial_prompt"]
        mention_event_ts = session_info.get("mention_event_ts")

        # pending_sessions から active_sessions へ移動
        _pending_sessions.pop(thread_ts, None)

        slack_thread_id = f"cmd:{channel_id}:{thread_ts}"

        _active_sessions[thread_ts] = {
            "branch": branch,
            "channel_id": channel_id,
            "user_id": user_id,
            "user_name": user_name,
            "slack_thread_id": slack_thread_id,
            "agent_name": agent_name,
        }

        # 処理開始のリアクションを元メンションに追加
        if mention_event_ts:
            try:
                await client.reactions_add(
                    channel=channel_id,
                    timestamp=mention_event_ts,
                    name="hourglass_flowing_sand",
                )
            except Exception:
                pass

        logger.info(
            "auto_execute_started",
            branch=branch,
            channel_id=channel_id,
            thread_ts=thread_ts,
            prompt_length=len(initial_prompt),
        )

        # 設定完了メッセージ
        agent_display = "通常モード"
        if agent_name:
            agents = get_available_agents()
            agent_display = next(
                (a["display_name"] for a in agents if a["name"] == agent_name),
                agent_name
            )

        await client.chat_postMessage(
            channel=channel_id,
            thread_ts=thread_ts,
            text=f"設定完了: ブランチ `{branch}` / エージェント `{agent_display}`\n\n自動的に処理を開始します...",
        )

        # チャンネル情報を取得
        try:
            channel_info = await client.conversations_info(channel=channel_id)
            channel_name = channel_info["channel"].get("name", "unknown")
        except Exception:
            channel_name = "unknown"

        # session_handler.process_prompt を呼び出し
        try:
            await self.session_handler.process_prompt(
                client=client,
                channel_id=channel_id,
                thread_ts=thread_ts,
                slack_thread_id=slack_thread_id,
                user_id=user_id,
                user_name=user_name,
                channel_name=channel_name,
                prompt=initial_prompt,
                branch=branch,
                agent_name=agent_name,
            )

            # 成功リアクション
            if mention_event_ts:
                try:
                    await client.reactions_remove(
                        channel=channel_id,
                        timestamp=mention_event_ts,
                        name="hourglass_flowing_sand",
                    )
                    await client.reactions_add(
                        channel=channel_id,
                        timestamp=mention_event_ts,
                        name="white_check_mark",
                    )
                except Exception:
                    pass

        except Exception as e:
            logger.error("auto_execute_failed", error=str(e), exc_info=True)
            await client.chat_postMessage(
                channel=channel_id,
                thread_ts=thread_ts,
                text=f"エラーが発生しました: {str(e)}",
            )
            # エラーリアクション
            if mention_event_ts:
                try:
                    await client.reactions_add(
                        channel=channel_id,
                        timestamp=mention_event_ts,
                        name="x",
                    )
                except Exception:
                    pass
