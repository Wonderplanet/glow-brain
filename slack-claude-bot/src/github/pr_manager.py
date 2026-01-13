"""GitHub PR management."""

import subprocess
from pathlib import Path
from typing import Optional

import structlog

from ..config import Config

logger = structlog.get_logger()


class SlackContext:
    """Slack context information for PR."""

    def __init__(
        self,
        channel_name: str,
        thread_link: str,
        user_name: str,
    ):
        self.channel_name = channel_name
        self.thread_link = thread_link
        self.user_name = user_name


class GitHubPRManager:
    """Manage GitHub PR creation."""

    def __init__(self):
        """Initialize GitHub PR manager."""
        self.repo_owner = Config.GITHUB_REPO_OWNER
        self.repo_name = Config.GITHUB_REPO_NAME
        self.base_branch = Config.GITHUB_BASE_BRANCH

    async def create_pr_if_changes(
        self,
        worktree_path: Path,
        session_id: str,
        slack_context: SlackContext,
        summary: str,
    ) -> Optional[tuple[str, str, int]]:
        """Create PR if there are changes in worktree.

        Args:
            worktree_path: Path to worktree
            session_id: Session ID
            slack_context: Slack context information
            summary: Summary of changes

        Returns:
            Tuple of (branch_name, pr_url, pr_number) or None if no changes
        """
        # Check if there are changes
        if not self._has_changes(worktree_path):
            logger.info("no_changes_detected", worktree=str(worktree_path))
            return None

        branch_name = f"slack-bot/{session_id}"

        try:
            # Create commit
            commit_message = self._build_commit_message(slack_context, summary)
            await self._commit_changes(worktree_path, commit_message)

            # Create and push branch
            await self._create_and_push_branch(worktree_path, branch_name)

            # Create PR
            pr_body = self._build_pr_body(slack_context, summary)
            pr_url, pr_number = await self._create_pr(
                worktree_path,
                branch_name,
                summary,
                pr_body,
            )

            logger.info(
                "pr_created",
                session_id=session_id,
                branch=branch_name,
                pr_url=pr_url,
                pr_number=pr_number,
            )

            return (branch_name, pr_url, pr_number)

        except Exception as e:
            logger.error(
                "pr_creation_failed",
                session_id=session_id,
                error=str(e),
            )
            raise

    def _has_changes(self, worktree_path: Path) -> bool:
        """Check if worktree has changes.

        Args:
            worktree_path: Path to worktree

        Returns:
            True if there are changes
        """
        try:
            result = subprocess.run(
                ["git", "status", "--porcelain"],
                cwd=worktree_path,
                capture_output=True,
                text=True,
                check=True,
            )
            return bool(result.stdout.strip())
        except subprocess.CalledProcessError:
            return False

    async def _commit_changes(self, worktree_path: Path, message: str) -> None:
        """Commit all changes in worktree.

        Args:
            worktree_path: Path to worktree
            message: Commit message
        """
        # Add all changes
        subprocess.run(
            ["git", "add", "-A"],
            cwd=worktree_path,
            check=True,
        )

        # Commit
        subprocess.run(
            ["git", "commit", "-m", message],
            cwd=worktree_path,
            check=True,
        )

        logger.info("changes_committed", worktree=str(worktree_path))

    async def _create_and_push_branch(
        self,
        worktree_path: Path,
        branch_name: str,
    ) -> None:
        """Create and push branch.

        Args:
            worktree_path: Path to worktree
            branch_name: Branch name
        """
        # Create branch
        subprocess.run(
            ["git", "checkout", "-b", branch_name],
            cwd=worktree_path,
            check=True,
        )

        # Push to remote
        subprocess.run(
            ["git", "push", "-u", "origin", branch_name],
            cwd=worktree_path,
            check=True,
        )

        logger.info("branch_pushed", branch=branch_name)

    async def _create_pr(
        self,
        worktree_path: Path,
        branch_name: str,
        title: str,
        body: str,
    ) -> tuple[str, int]:
        """Create PR using gh CLI.

        Args:
            worktree_path: Path to worktree
            branch_name: Branch name
            title: PR title
            body: PR body

        Returns:
            Tuple of (pr_url, pr_number)
        """
        result = subprocess.run(
            [
                "gh",
                "pr",
                "create",
                "--base",
                self.base_branch,
                "--head",
                branch_name,
                "--title",
                title,
                "--body",
                body,
            ],
            cwd=worktree_path,
            capture_output=True,
            text=True,
            check=True,
        )

        pr_url = result.stdout.strip()

        # Extract PR number from URL
        pr_number = int(pr_url.split("/")[-1])

        return (pr_url, pr_number)

    def _build_commit_message(
        self,
        ctx: SlackContext,
        summary: str,
    ) -> str:
        """Build commit message with Slack context.

        Args:
            ctx: Slack context
            summary: Summary of changes

        Returns:
            Commit message
        """
        return f"""[Slack Bot] {summary}

Slack: #{ctx.channel_name}
Thread: {ctx.thread_link}
User: @{ctx.user_name}

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
"""

    def _build_pr_body(
        self,
        ctx: SlackContext,
        summary: str,
    ) -> str:
        """Build PR body with Slack context.

        Args:
            ctx: Slack context
            summary: Summary of changes

        Returns:
            PR body
        """
        return f"""## Slack Bot による自動PR

### 対応元
- **チャンネル**: #{ctx.channel_name}
- **スレッド**: {ctx.thread_link}
- **リクエストユーザー**: @{ctx.user_name}

### 対応内容
{summary}

---
🤖 Generated by Slack-Claude Bot
"""
