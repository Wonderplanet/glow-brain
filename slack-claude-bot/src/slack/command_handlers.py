"""Slack slash command handlers."""

import asyncio
from pathlib import Path

import structlog
from slack_sdk import WebClient

from ..claude.executor import ClaudeExecutor
from ..github.pr_manager import GitHubPRManager, SlackContext
from ..session.manager import SessionManager
from ..worktree.manager import WorktreeManager
from .views import build_branch_select_message

logger = structlog.get_logger()

# Store pending sessions waiting for thread messages
# {message_ts: {"branch": str, "channel_id": str, "user_id": str, "user_name": str}}
_pending_sessions = {}


class CommandHandlers:
    """Handle Slack slash commands and interactive components."""

    def __init__(
        self,
        session_manager: SessionManager,
        claude_executor: ClaudeExecutor,
        github_manager: GitHubPRManager,
        worktree_manager: WorktreeManager,
    ):
        """Initialize command handlers.

        Args:
            session_manager: Session manager
            claude_executor: Claude executor
            github_manager: GitHub PR manager
            worktree_manager: Worktree manager (for getting available versions)
        """
        self.session_manager = session_manager
        self.claude_executor = claude_executor
        self.github_manager = github_manager
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
            # Check if this is a thread reply to a pending session
            thread_ts = event.get("thread_ts")
            if not thread_ts or thread_ts not in _pending_sessions:
                return

            # Get pending session info
            session_info = _pending_sessions.pop(thread_ts)
            branch = session_info["branch"]
            channel_id = session_info["channel_id"]
            user_id = session_info["user_id"]
            user_name = session_info["user_name"]

            prompt = event["text"]

            logger.info(
                "thread_prompt_received",
                branch=branch,
                channel_id=channel_id,
                user_id=user_id,
                prompt_length=len(prompt),
            )

            # Create session
            import time
            timestamp = str(int(time.time() * 1000))
            slack_thread_id = f"cmd:{channel_id}:{timestamp}"

            session = await self.session_manager.get_or_create_session(
                slack_thread_id=slack_thread_id,
                slack_channel_id=channel_id,
                slack_user_id=user_id,
                slack_channel_name="command",
                slack_user_name=user_name,
                branch=branch,
            )

            is_first = not session.claude_session_started

            # Send processing message
            response = await client.chat_postMessage(
                channel=channel_id,
                thread_ts=thread_ts,
                text=f"🔄 処理中です... (ブランチ: `{branch}`)",
            )

            processing_ts = response["ts"]

            # Execute Claude
            result = await self.claude_executor.execute(
                prompt=prompt,
                worktree_path=Path(session.worktree_path),
                session_id=session.id,
                is_first_message=is_first,
            )

            if is_first:
                self.session_manager.mark_session_started(session.id)

            if result.is_error:
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text=f"❌ エラーが発生しました:\n```\n{result.error_message}\n```",
                )
                return

            # Send response
            if result.output and result.output.strip():
                await self._send_response(
                    client=client,
                    channel=channel_id,
                    thread_ts=thread_ts,
                    output=result.output,
                )
            else:
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text="（Claudeから応答がありませんでした。）",
                )

            # Check for changes and create PR
            slack_context = SlackContext(
                channel_name="command",
                thread_link=None,
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

                self.session_manager.update_github_pr(
                    session_id=session.id,
                    branch=branch_name,
                    pr_url=pr_url,
                    pr_number=pr_number,
                )

                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text=f"📝 PRを作成しました: {pr_url}",
                )

            await client.chat_postMessage(
                channel=channel_id,
                thread_ts=thread_ts,
                text="✅ 処理が完了しました",
            )

        except Exception as e:
            logger.error("thread_message_failed", error=str(e), exc_info=True)

    async def _send_response(
        self,
        client: WebClient,
        channel: str,
        thread_ts: str,
        output: str,
    ) -> None:
        """Send response to Slack.

        Args:
            client: Slack WebClient
            channel: Channel ID
            thread_ts: Thread timestamp
            output: Output text
        """
        # Split long messages
        max_length = 4000
        chunks = self._split_text(output, max_length)

        for chunk in chunks:
            await client.chat_postMessage(
                channel=channel,
                text=chunk,
                thread_ts=thread_ts,
            )

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
