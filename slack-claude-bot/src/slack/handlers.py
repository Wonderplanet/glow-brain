"""Slack event handlers."""

from typing import Optional

import structlog
from slack_sdk import WebClient

from ..worktree.manager import WorktreeManager
from .command_handlers import _active_sessions, _pending_sessions
from .session_handler import SessionHandler
from .views import build_branch_select_message

logger = structlog.get_logger()


class SlackHandlers:
    """Handle Slack events."""

    def __init__(
        self,
        session_handler: SessionHandler,
        worktree_manager: WorktreeManager,
    ):
        """Initialize handlers.

        Args:
            session_handler: Common session handler
            worktree_manager: Worktree manager (for getting available versions)
        """
        self.session_handler = session_handler
        self.worktree_manager = worktree_manager

    async def handle_app_mention(
        self,
        event: dict,
        client: WebClient,
        say,
    ) -> None:
        """Handle app mention event.

        Args:
            event: Slack event data
            client: Slack WebClient
            say: Function to send messages
        """
        channel_id = event["channel"]
        user_id = event["user"]
        text = event["text"]
        event_ts = event["ts"]
        thread_ts = event.get("thread_ts")  # スレッド内メンションの場合はスレッドのts

        # スレッド内メンションかチャンネル直下かを判定
        if thread_ts is None:
            # チャンネル直下でのメンション
            # branch:xxx の指定があるかチェック
            branch, remaining_text = self._extract_branch(text)

            if branch:
                # branch指定あり → 既存フロー（即実行）
                await self._handle_immediate_execution(
                    event=event,
                    client=client,
                    say=say,
                    branch=branch,
                    text=remaining_text,
                )
            else:
                # branch指定なし → UI選択フローを開始
                await self._handle_channel_mention_with_ui(
                    event=event,
                    client=client,
                    say=say,
                )
        else:
            # 既存スレッド内でのメンション → 従来の処理
            await self._handle_thread_mention(
                event=event,
                client=client,
                say=say,
            )

    async def _handle_immediate_execution(
        self,
        event: dict,
        client: WebClient,
        say,
        branch: str,
        text: str,
    ) -> None:
        """branch:xxx形式で即実行するフロー"""
        channel_id = event["channel"]
        user_id = event["user"]
        event_ts = event["ts"]
        thread_ts = event_ts  # チャンネル直下なので event_ts が thread_ts

        # Create thread ID
        slack_thread_id = f"{channel_id}:{thread_ts}"

        # Add processing reaction
        await self._add_reaction(client, channel_id, event_ts, "hourglass_flowing_sand")

        try:
            # Get channel and user info
            channel_info = await self._get_channel_info(client, channel_id)
            user_info = await self._get_user_info(client, user_id)

            channel_name = channel_info.get("name", "unknown")
            user_name = user_info.get("name", "unknown")

            # Remove bot mention from text
            prompt = self._extract_prompt(text)

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

            # Add success reaction
            await self._remove_reaction(client, channel_id, event_ts, "hourglass_flowing_sand")
            await self._add_reaction(client, channel_id, event_ts, "white_check_mark")

        except Exception as e:
            logger.error("handle_immediate_execution_failed", error=str(e), exc_info=True)
            await say(
                text=f"エラーが発生しました: {str(e)}",
                thread_ts=thread_ts,
            )
            await self._add_reaction(client, channel_id, event_ts, "x")

    async def _handle_channel_mention_with_ui(
        self,
        event: dict,
        client: WebClient,
        say,
    ) -> None:
        """チャンネル直下のメンションでUI選択フローを開始"""
        channel_id = event["channel"]
        user_id = event["user"]
        text = event["text"]
        event_ts = event["ts"]

        # プロンプトを抽出
        prompt = self._extract_prompt(text)

        # スレッドを作成してブランチ選択UIを投稿
        versions = self.worktree_manager.get_available_versions()
        current_version = versions[0] if versions else None

        message = build_branch_select_message(
            versions=versions,
            current_version=current_version,
        )

        # スレッドへの返信としてUIを投稿（これによりスレッドが作成される）
        result = await client.chat_postMessage(
            channel=channel_id,
            thread_ts=event_ts,  # 元のメンションへの返信
            **message,
        )

        # ユーザー情報を取得
        user_info = await self._get_user_info(client, user_id)
        user_name = user_info.get("name", "unknown")

        # pending_session に登録（プロンプトも保存）
        # キーは元のメンションのts（スレッドの親ts）
        _pending_sessions[event_ts] = {
            "branch": None,  # まだ未選択
            "channel_id": channel_id,
            "user_id": user_id,
            "user_name": user_name,
            "agent_name": None,
            "initial_prompt": prompt,  # 元のプロンプトを保存
            "mention_event_ts": event_ts,  # リアクション追加用
            "source": "mention",  # コマンド経由との区別
        }

        logger.info(
            "channel_mention_ui_flow_started",
            channel_id=channel_id,
            thread_ts=event_ts,
            user_id=user_id,
            prompt_length=len(prompt),
        )

    async def _handle_thread_mention(
        self,
        event: dict,
        client: WebClient,
        say,
    ) -> None:
        """スレッド内でのメンション（既存の処理）"""
        channel_id = event["channel"]
        thread_ts = event.get("thread_ts") or event["ts"]
        user_id = event["user"]
        text = event["text"]

        # Check if this thread is managed by command handlers (in-memory check first)
        if thread_ts in _pending_sessions or thread_ts in _active_sessions:
            logger.info("skipping_command_thread_in_memory", thread_ts=thread_ts)
            return

        # Check if this thread is managed by command handlers (DB check)
        cmd_slack_thread_id = f"cmd:{channel_id}:{thread_ts}"
        if self.session_handler.session_manager.db.get_session_by_slack_thread(cmd_slack_thread_id):
            # This thread is managed by command handlers, skip
            logger.info("skipping_command_thread", slack_thread_id=cmd_slack_thread_id)
            return

        # Extract branch specification from text
        branch, text = self._extract_branch(text)

        # Create thread ID
        slack_thread_id = f"{channel_id}:{thread_ts}"

        # Add processing reaction
        await self._add_reaction(client, channel_id, event["ts"], "hourglass_flowing_sand")

        try:
            # Get channel and user info
            channel_info = await self._get_channel_info(client, channel_id)
            user_info = await self._get_user_info(client, user_id)

            channel_name = channel_info.get("name", "unknown")
            user_name = user_info.get("name", "unknown")

            # Remove bot mention from text
            prompt = self._extract_prompt(text)

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

            # Add success reaction
            await self._remove_reaction(client, channel_id, event["ts"], "hourglass_flowing_sand")
            await self._add_reaction(client, channel_id, event["ts"], "white_check_mark")

        except Exception as e:
            logger.error("handle_mention_failed", error=str(e), exc_info=True)
            await say(
                text=f"エラーが発生しました: {str(e)}",
                thread_ts=thread_ts,
            )
            await self._add_reaction(client, channel_id, event["ts"], "x")

    def _extract_branch(self, text: str) -> tuple[Optional[str], str]:
        """Extract branch specification from text.

        Args:
            text: Raw text that may contain branch specification

        Returns:
            Tuple of (branch_name or None, remaining_text)
        """
        import re
        match = re.search(r'branch:(\S+)', text)
        if match:
            branch = match.group(1)
            remaining = re.sub(r'branch:\S+\s*', '', text)
            return branch, remaining.strip()
        return None, text

    def _extract_prompt(self, text: str) -> str:
        """Extract prompt from mention text.

        Args:
            text: Raw text with mention

        Returns:
            Cleaned prompt
        """
        # Remove bot mention (e.g., <@U123456>)
        import re
        cleaned = re.sub(r'<@[UW][A-Z0-9]+>', '', text)
        return cleaned.strip()

    async def _add_reaction(
        self,
        client: WebClient,
        channel: str,
        timestamp: str,
        name: str,
    ) -> None:
        """Add reaction to message."""
        try:
            await client.reactions_add(
                channel=channel,
                timestamp=timestamp,
                name=name,
            )
        except Exception as e:
            logger.warning("add_reaction_failed", error=str(e))

    async def _remove_reaction(
        self,
        client: WebClient,
        channel: str,
        timestamp: str,
        name: str,
    ) -> None:
        """Remove reaction from message."""
        try:
            await client.reactions_remove(
                channel=channel,
                timestamp=timestamp,
                name=name,
            )
        except Exception as e:
            logger.warning("remove_reaction_failed", error=str(e))

    async def _get_channel_info(
        self,
        client: WebClient,
        channel_id: str,
    ) -> dict:
        """Get channel information."""
        try:
            result = await client.conversations_info(channel=channel_id)
            return result["channel"]
        except Exception as e:
            logger.warning("get_channel_info_failed", error=str(e))
            return {}

    async def _get_user_info(
        self,
        client: WebClient,
        user_id: str,
    ) -> dict:
        """Get user information."""
        try:
            result = await client.users_info(user=user_id)
            return result["user"]
        except Exception as e:
            logger.warning("get_user_info_failed", error=str(e))
            return {}
