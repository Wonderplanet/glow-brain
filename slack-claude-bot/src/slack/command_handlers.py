"""Slack slash command handlers."""

import asyncio
from pathlib import Path

import structlog
from slack_sdk import WebClient

from ..claude.executor import ClaudeExecutor
from ..github.pr_manager import GitHubPRManager, SlackContext
from ..session.manager import SessionManager
from ..worktree.manager import WorktreeManager
from .views import build_glow_brain_modal

logger = structlog.get_logger()


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
        """Handle /glow-brain command - open modal with branch selector.

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

            # Get current version from versions.json (optional enhancement)
            # For now, just use the first version as default
            current_version = versions[0] if versions else None

            # Build modal view
            view = build_glow_brain_modal(
                versions=versions,
                current_version=current_version,
                channel_id=body["channel_id"],
            )

            # Open modal
            await client.views_open(
                trigger_id=body["trigger_id"],
                view=view,
            )

            logger.info(
                "modal_opened",
                user_id=body["user_id"],
            )

        except FileNotFoundError as e:
            logger.error(
                "versions_file_not_found",
                error=str(e),
            )
            # Send ephemeral error message
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

    async def handle_modal_submission(
        self,
        ack,
        body: dict,
        client: WebClient,
        view: dict,
    ) -> None:
        """Handle modal submission - start Claude session.

        Args:
            ack: Acknowledge function
            body: Slack view submission payload
            client: Slack WebClient
            view: Submitted view data
        """
        # Acknowledge immediately to avoid timeout
        await ack()

        # Process in background to avoid Slack timeout
        asyncio.create_task(
            self._process_modal_submission(body, client, view)
        )

    async def _process_modal_submission(
        self,
        body: dict,
        client: WebClient,
        view: dict,
    ) -> None:
        """Process modal submission in background.

        Args:
            body: Slack view submission payload
            client: Slack WebClient
            view: Submitted view data
        """
        try:
            # Extract values from modal
            values = view["state"]["values"]

            branch = values["branch_block"]["branch_select"]["selected_option"]["value"]
            prompt = values["prompt_block"]["prompt_input"]["value"]

            user_id = body["user"]["id"]
            user_name = body["user"]["name"]

            # Get channel ID from private_metadata
            channel_id = view.get("private_metadata", "")

            logger.info(
                "modal_submitted",
                user_id=user_id,
                channel_id=channel_id,
                branch=branch,
                prompt_length=len(prompt),
            )

            # Create a pseudo thread_id for this command-based session
            import time
            timestamp = str(int(time.time() * 1000))
            slack_thread_id = f"cmd:{channel_id}:{timestamp}"

            # Get or create session
            session = await self.session_manager.get_or_create_session(
                slack_thread_id=slack_thread_id,
                slack_channel_id=channel_id,
                slack_user_id=user_id,
                slack_channel_name="command",
                slack_user_name=user_name,
                branch=branch,
            )

            # Check if this is the first message
            is_first = not session.claude_session_started

            # Send "processing" message
            response = await client.chat_postMessage(
                channel=channel_id,
                text=f"🔄 処理中です... (ブランチ: `{branch}`)\n```\n{prompt}\n```",
            )

            message_ts = response["ts"]

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
                await client.chat_postMessage(
                    channel=channel_id,
                    text=f"❌ エラーが発生しました:\n```\n{result.error_message}\n```",
                    thread_ts=message_ts,
                )
                return

            # Send response
            if result.output and result.output.strip():
                await self._send_response(
                    client=client,
                    channel=channel_id,
                    thread_ts=message_ts,
                    output=result.output,
                )
            else:
                await client.chat_postMessage(
                    channel=channel_id,
                    text="（Claudeから応答がありませんでした。もう一度お試しください。）",
                    thread_ts=message_ts,
                )

            # Check for changes and create PR if needed
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

                # Update session with PR info
                self.session_manager.update_github_pr(
                    session_id=session.id,
                    branch=branch_name,
                    pr_url=pr_url,
                    pr_number=pr_number,
                )

                # Send PR link
                await client.chat_postMessage(
                    channel=channel_id,
                    text=f"📝 PRを作成しました: {pr_url}",
                    thread_ts=message_ts,
                )

            # Send completion message
            await client.chat_postMessage(
                channel=channel_id,
                text="✅ 処理が完了しました",
                thread_ts=message_ts,
            )

        except Exception as e:
            logger.error("modal_submission_failed", error=str(e), exc_info=True)

            # Try to send error to user in the channel
            try:
                # Get channel_id from view if available
                channel_id = view.get("private_metadata", "")
                if channel_id:
                    await client.chat_postMessage(
                        channel=channel_id,
                        text=f"エラーが発生しました: {str(e)}",
                    )
            except Exception:
                pass

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
