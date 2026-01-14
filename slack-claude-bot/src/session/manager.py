"""Session lifecycle management."""

import asyncio
import uuid
from datetime import datetime, timedelta
from pathlib import Path
from typing import Optional

import structlog

from ..config import Config
from .db import SessionDB

logger = structlog.get_logger()


class Session:
    """Session data model."""

    def __init__(self, data: dict):
        self.id: str = data["id"]
        self.slack_thread_id: str = data["slack_thread_id"]
        self.slack_channel_id: str = data["slack_channel_id"]
        self.slack_channel_name: Optional[str] = data.get("slack_channel_name")
        self.slack_user_id: str = data["slack_user_id"]
        self.slack_user_name: Optional[str] = data.get("slack_user_name")
        self.slack_thread_link: Optional[str] = data.get("slack_thread_link")
        self.claude_session_started: bool = bool(data.get("claude_session_started", 0))
        self.worktree_path: str = data["worktree_path"]
        self.github_branch: Optional[str] = data.get("github_branch")
        self.github_pr_url: Optional[str] = data.get("github_pr_url")
        self.github_pr_number: Optional[int] = data.get("github_pr_number")
        self.status: str = data["status"]
        self.created_at: datetime = data["created_at"]
        self.last_activity: datetime = data["last_activity"]
        self.expires_at: datetime = data["expires_at"]


class SessionManager:
    """Manage session lifecycle."""

    def __init__(
        self,
        db: SessionDB,
        worktree_manager,
        max_sessions: int,
        ttl_hours: int,
    ):
        """Initialize session manager.

        Args:
            db: Database interface
            worktree_manager: Worktree manager instance
            max_sessions: Maximum concurrent sessions
            ttl_hours: Session TTL in hours
        """
        self.db = db
        self.worktree_manager = worktree_manager
        self.max_sessions = max_sessions
        self.ttl_hours = ttl_hours
        self._lock = asyncio.Lock()
        self._semaphore = asyncio.Semaphore(max_sessions)

    async def get_or_create_session(
        self,
        slack_thread_id: str,
        slack_channel_id: str,
        slack_user_id: str,
        slack_channel_name: Optional[str] = None,
        slack_user_name: Optional[str] = None,
    ) -> Session:
        """Get existing session or create new one.

        Args:
            slack_thread_id: Slack thread ID (format: channel_id:thread_ts)
            slack_channel_id: Slack channel ID
            slack_user_id: Slack user ID
            slack_channel_name: Slack channel name (optional)
            slack_user_name: Slack user name (optional)

        Returns:
            Session object

        Raises:
            RuntimeError: If max concurrent sessions exceeded
        """
        async with self._lock:
            # Check for existing session
            session_data = self.db.get_session_by_slack_thread(slack_thread_id)

            if session_data:
                # Update last activity
                self.db.update_last_activity(session_data["id"])
                logger.info(
                    "session_resumed",
                    session_id=session_data["id"],
                    slack_thread_id=slack_thread_id,
                )
                return Session(session_data)

            # Cleanup expired sessions
            await self._cleanup_expired_sessions()

            # Check concurrent session limit
            active_count = self.db.get_active_session_count()
            if active_count >= self.max_sessions:
                raise RuntimeError(
                    f"Maximum concurrent sessions ({self.max_sessions}) reached"
                )

            # Create new session
            session_id = str(uuid.uuid4())[:8]
            expires_at = datetime.now() + timedelta(hours=self.ttl_hours)

            # Generate Slack thread link
            thread_ts = slack_thread_id.split(":")[-1]
            slack_thread_link = Config.get_slack_thread_link(
                slack_channel_id, thread_ts
            )

            # Create worktree
            worktree_path = self.worktree_manager.create_worktree(session_id)

            # claude_session_started will be set to True when first message is processed
            # This allows is_first_message check to work correctly
            claude_session_started = False

            # Save to database
            self.db.create_session(
                session_id=session_id,
                slack_thread_id=slack_thread_id,
                slack_channel_id=slack_channel_id,
                slack_user_id=slack_user_id,
                worktree_path=str(worktree_path),
                expires_at=expires_at,
                slack_channel_name=slack_channel_name,
                slack_user_name=slack_user_name,
                slack_thread_link=slack_thread_link,
                claude_session_started=claude_session_started,
            )

            logger.info(
                "session_created",
                session_id=session_id,
                slack_thread_id=slack_thread_id,
                worktree_path=str(worktree_path),
            )

            # Reload from DB to get complete data
            session_data = self.db.get_session_by_slack_thread(slack_thread_id)
            return Session(session_data)

    async def _cleanup_expired_sessions(self) -> None:
        """Cleanup expired sessions."""
        expired_sessions = self.db.get_expired_sessions()

        for session_data in expired_sessions:
            session_id = session_data["id"]
            worktree_path = Path(session_data["worktree_path"])

            # Remove worktree (Claude session cleanup is automatic)
            self.worktree_manager.remove_worktree(worktree_path)

            # Mark as expired in DB
            self.db.mark_session_expired(session_id)

            logger.info("session_cleaned_up", session_id=session_id)

    def mark_session_started(
        self,
        session_id: str,
    ) -> None:
        """Mark Claude session as started.

        Args:
            session_id: Session ID
        """
        self.db.mark_session_started(session_id)

    def update_github_pr(
        self,
        session_id: str,
        branch: str,
        pr_url: str,
        pr_number: int,
    ) -> None:
        """Update GitHub PR information for session.

        Args:
            session_id: Session ID
            branch: Branch name
            pr_url: PR URL
            pr_number: PR number
        """
        self.db.update_github_pr(session_id, branch, pr_url, pr_number)
