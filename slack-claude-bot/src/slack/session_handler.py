"""Common session processing logic for Slack handlers."""

import asyncio
from pathlib import Path
from typing import Dict, Optional

import structlog
from slack_sdk import WebClient

from ..claude.executor import ClaudeExecutor
from ..github.pr_manager import GitHubPRManager, SlackContext
from ..session.manager import SessionManager

logger = structlog.get_logger()


class SessionHandler:
    """Handle common session processing logic."""

    def __init__(
        self,
        session_manager: SessionManager,
        claude_executor: ClaudeExecutor,
        github_manager: GitHubPRManager,
    ):
        """Initialize session handler.

        Args:
            session_manager: Session manager
            claude_executor: Claude executor
            github_manager: GitHub PR manager
        """
        self.session_manager = session_manager
        self.claude_executor = claude_executor
        self.github_manager = github_manager
        self._processing_locks: Dict[str, asyncio.Lock] = {}

    def _get_thread_lock(self, thread_ts: str) -> asyncio.Lock:
        """Get or create a lock for the given thread.

        Args:
            thread_ts: Thread timestamp

        Returns:
            Lock for the thread
        """
        if thread_ts not in self._processing_locks:
            self._processing_locks[thread_ts] = asyncio.Lock()
        return self._processing_locks[thread_ts]

    def _get_branch_from_worktree(self, worktree_path: str) -> str:
        """Get current branch from worktree.

        Args:
            worktree_path: Path to worktree

        Returns:
            Current branch name, or 'main' if unable to determine
        """
        import subprocess

        try:
            result = subprocess.run(
                ["git", "rev-parse", "--abbrev-ref", "HEAD"],
                cwd=worktree_path,
                capture_output=True,
                text=True,
                timeout=5,
            )
            return result.stdout.strip() or "main"
        except Exception:
            return "main"

    async def process_prompt(
        self,
        client: WebClient,
        channel_id: str,
        thread_ts: str,
        slack_thread_id: str,
        user_id: str,
        user_name: str,
        channel_name: str,
        prompt: str,
        branch: Optional[str] = None,
        agent_name: Optional[str] = None,
    ) -> None:
        """Process prompt and execute Claude.

        Args:
            client: Slack WebClient
            channel_id: Slack channel ID
            thread_ts: Slack thread timestamp
            slack_thread_id: Unique session ID (format: channel_id:thread_ts or cmd:channel_id:thread_ts)
            user_id: Slack user ID
            user_name: Slack user name
            channel_name: Slack channel name
            prompt: User prompt
            branch: Git branch to checkout for worktree (optional)
            agent_name: Agent name for Claude CLI --agent option (optional)
        """
        lock = self._get_thread_lock(thread_ts)

        async with lock:
            try:
                # 1. Get or create session
                session = await self.session_manager.get_or_create_session(
                    slack_thread_id=slack_thread_id,
                    slack_channel_id=channel_id,
                    slack_user_id=user_id,
                    slack_channel_name=channel_name,
                    slack_user_name=user_name,
                    branch=branch,
                    agent_name=agent_name,
                )

                is_first = not session.claude_session_started

                # 2. Send processing message
                # Get current commit hash
                commit_hash = self.session_manager.worktree_manager.get_commit_hash(
                    Path(session.worktree_path)
                )

                # Get actual branch from worktree if branch parameter is None
                display_branch = branch if branch is not None else self._get_branch_from_worktree(session.worktree_path)

                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text=f"🔄 処理中です... (セッション: `{session.id}`, ブランチ: `{display_branch}`, コミット: `{commit_hash}`)",
                )

                # 3. Execute Claude
                result = await self.claude_executor.execute(
                    prompt=prompt,
                    worktree_path=Path(session.worktree_path),
                    session_id=session.id,
                    is_first_message=is_first,
                    agent_name=agent_name or session.agent_name,
                )

                if is_first:
                    self.session_manager.mark_session_started(session.id)

                # 4. Send response
                if result.is_error:
                    await client.chat_postMessage(
                        channel=channel_id,
                        thread_ts=thread_ts,
                        text=f"❌ エラーが発生しました:\n```\n{result.error_message}\n```",
                    )
                    return

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

                # 5. Check for changes and create PR
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

                # 6. Send completion message
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text="✅ 処理が完了しました",
                )

            except Exception as e:
                logger.error("process_prompt_failed", error=str(e), exc_info=True)
                await client.chat_postMessage(
                    channel=channel_id,
                    thread_ts=thread_ts,
                    text=f"エラーが発生しました: {str(e)}",
                )

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
