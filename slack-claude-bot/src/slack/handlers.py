"""Slack event handlers."""

from pathlib import Path
from typing import Optional

import structlog
from slack_sdk import WebClient

from ..claude.executor import ClaudeExecutor
from ..github.pr_manager import GitHubPRManager, SlackContext
from ..session.manager import SessionManager

logger = structlog.get_logger()


class SlackHandlers:
    """Handle Slack events."""

    def __init__(
        self,
        session_manager: SessionManager,
        claude_executor: ClaudeExecutor,
        github_manager: GitHubPRManager,
    ):
        """Initialize handlers.

        Args:
            session_manager: Session manager
            claude_executor: Claude executor
            github_manager: GitHub PR manager
        """
        self.session_manager = session_manager
        self.claude_executor = claude_executor
        self.github_manager = github_manager

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
        thread_ts = event.get("thread_ts") or event["ts"]
        user_id = event["user"]
        text = event["text"]

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

            # Get or create session
            session = await self.session_manager.get_or_create_session(
                slack_thread_id=slack_thread_id,
                slack_channel_id=channel_id,
                slack_user_id=user_id,
                slack_channel_name=channel_name,
                slack_user_name=user_name,
            )

            # Remove bot mention from text
            prompt = self._extract_prompt(text)

            # Check if this is the first message
            is_first = not session.claude_session_started

            # Execute Claude
            result = await self.claude_executor.execute(
                prompt=prompt,
                worktree_path=Path(session.worktree_path),
                session_id=session.id,
                is_first_message=is_first,
            )

            # Mark session as started if first message
            if is_first:
                self.session_manager.mark_session_started(session.id)

            if result.is_error:
                await say(
                    text=f"エラーが発生しました: {result.error_message}",
                    thread_ts=thread_ts,
                )
                await self._add_reaction(client, channel_id, event["ts"], "x")
                return

            # Send response
            if result.output and result.output.strip():
                await self._send_response(
                    say=say,
                    thread_ts=thread_ts,
                    output=result.output,
                )
            else:
                await say(
                    text="（Claudeから応答がありませんでした。もう一度お試しください。）",
                    thread_ts=thread_ts,
                )

            # Check for changes and create PR if needed
            slack_context = SlackContext(
                channel_name=channel_name,
                thread_link=session.slack_thread_link,
                user_name=user_name,
            )

            pr_info = await self.github_manager.create_pr_if_changes(
                worktree_path=Path(session.worktree_path),
                session_id=session.id,
                slack_context=slack_context,
                summary=f"{prompt[:100]}...",
            )

            if pr_info:
                branch_name, pr_url, pr_number = pr_info

                # Update session with PR info
                self.session_manager.update_github_pr(
                    session_id=session.id,
                    branch=branch_name,
                    pr_url=pr_url,
                    pr_number=pr_number,
                )

                # Send PR link
                await say(
                    text=f"📝 PRを作成しました: {pr_url}",
                    thread_ts=thread_ts,
                )

            # Add success reaction
            await self._remove_reaction(client, channel_id, event["ts"], "hourglass_flowing_sand")
            await self._add_reaction(client, channel_id, event["ts"], "white_check_mark")

        except Exception as e:
            logger.error("handle_mention_failed", error=str(e))
            await say(
                text=f"エラーが発生しました: {str(e)}",
                thread_ts=thread_ts,
            )
            await self._add_reaction(client, channel_id, event["ts"], "x")

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

    async def _send_response(
        self,
        say,
        thread_ts: str,
        output: str,
    ) -> None:
        """Send response to Slack thread.

        Args:
            say: Slack say function
            thread_ts: Thread timestamp
            output: Output text
        """
        # Split long messages
        max_length = 4000
        chunks = self._split_text(output, max_length)

        for chunk in chunks:
            await say(text=chunk, thread_ts=thread_ts)

    def _split_text(self, text: str, max_length: int) -> list[str]:
        """Split text into chunks.

        Args:
            text: Text to split
            max_length: Maximum chunk length

        Returns:
            List of text chunks
        """
        if len(text) <= max_length:
            return [text]

        chunks = []
        current_chunk = ""

        for line in text.split('\n'):
            if len(current_chunk) + len(line) + 1 > max_length:
                if current_chunk:
                    chunks.append(current_chunk)
                current_chunk = line
            else:
                if current_chunk:
                    current_chunk += '\n'
                current_chunk += line

        if current_chunk:
            chunks.append(current_chunk)

        return chunks

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
