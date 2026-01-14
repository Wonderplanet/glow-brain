"""Slack slash command handlers."""

import asyncio

import structlog
from slack_sdk import WebClient

from ..worktree.manager import WorktreeManager
from .session_handler import SessionHandler
from .views import build_branch_select_message

logger = structlog.get_logger()

# Store pending sessions waiting for thread messages (before first prompt)
# {message_ts: {"branch": str, "channel_id": str, "user_id": str, "user_name": str}}
_pending_sessions = {}

# Store active sessions after first prompt (for session lookup)
# {thread_ts: {"branch": str, "channel_id": str, "user_id": str, "user_name": str, "slack_thread_id": str}}
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
        """Handle branch selection button click.

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

            logger.info(
                "branch_selected",
                branch=branch,
                user_id=user_id,
                channel_id=channel_id,
            )

            # Store pending session info
            _pending_sessions[message_ts] = {
                "branch": branch,
                "channel_id": channel_id,
                "user_id": user_id,
                "user_name": user_name,
            }

            # Reply in thread asking for prompt
            await client.chat_postMessage(
                channel=channel_id,
                thread_ts=message_ts,
                text=f"ブランチ `{branch}` を選択しました。\nこのスレッドにプロンプトを送信してください。",
            )

        except Exception as e:
            logger.error("branch_select_action_failed", error=str(e), exc_info=True)

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

            # Case 1: First message (in _pending_sessions)
            if thread_ts in _pending_sessions:
                session_info = _pending_sessions.pop(thread_ts)
                branch = session_info["branch"]
                user_name = session_info["user_name"]

                # Create slack_thread_id based on thread_ts
                slack_thread_id = f"cmd:{channel_id}:{thread_ts}"

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
                )

                # Store as active session for future messages
                _active_sessions[thread_ts] = {
                    "branch": branch,
                    "channel_id": channel_id,
                    "user_id": user_id,
                    "user_name": user_name,
                    "slack_thread_id": slack_thread_id,
                }

            # Case 2: Continuation message (in _active_sessions)
            elif thread_ts in _active_sessions:
                session_info = _active_sessions[thread_ts]
                slack_thread_id = session_info["slack_thread_id"]
                branch = session_info["branch"]
                user_name = session_info["user_name"]

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
                )

            else:
                # This thread is not managed by command handlers
                return

        except Exception as e:
            logger.error("thread_message_failed", error=str(e), exc_info=True)
