"""Database operations for session management."""

import sqlite3
from datetime import datetime
from pathlib import Path
from typing import Optional

import structlog

logger = structlog.get_logger()


class SessionDB:
    """SQLite database interface for session management."""

    def __init__(self, db_path: Path):
        """Initialize database connection.

        Args:
            db_path: Path to SQLite database file
        """
        self.db_path = db_path
        self._init_db()

    def _init_db(self) -> None:
        """Initialize database schema."""
        # Ensure parent directory exists
        self.db_path.parent.mkdir(parents=True, exist_ok=True)

        with sqlite3.connect(self.db_path) as conn:
            conn.execute("""
                CREATE TABLE IF NOT EXISTS sessions (
                    id TEXT PRIMARY KEY,
                    slack_thread_id TEXT UNIQUE NOT NULL,
                    slack_channel_id TEXT NOT NULL,
                    slack_channel_name TEXT,
                    slack_user_id TEXT NOT NULL,
                    slack_user_name TEXT,
                    slack_thread_link TEXT,
                    tmux_session_name TEXT,
                    worktree_path TEXT NOT NULL,
                    github_branch TEXT,
                    github_pr_url TEXT,
                    github_pr_number INTEGER,
                    status TEXT NOT NULL DEFAULT 'active',
                    created_at TIMESTAMP NOT NULL,
                    last_activity TIMESTAMP NOT NULL,
                    expires_at TIMESTAMP NOT NULL
                )
            """)

            conn.execute("""
                CREATE INDEX IF NOT EXISTS idx_slack_thread
                ON sessions(slack_thread_id)
            """)

            conn.execute("""
                CREATE INDEX IF NOT EXISTS idx_status
                ON sessions(status)
            """)

            conn.execute("""
                CREATE INDEX IF NOT EXISTS idx_expires
                ON sessions(expires_at)
            """)

        logger.info("database_initialized", db_path=str(self.db_path))

    def create_session(
        self,
        session_id: str,
        slack_thread_id: str,
        slack_channel_id: str,
        slack_user_id: str,
        worktree_path: str,
        expires_at: datetime,
        slack_channel_name: Optional[str] = None,
        slack_user_name: Optional[str] = None,
        slack_thread_link: Optional[str] = None,
        tmux_session_name: Optional[str] = None,
    ) -> None:
        """Create a new session.

        Args:
            session_id: Unique session ID
            slack_thread_id: Slack thread ID (format: channel_id:thread_ts)
            slack_channel_id: Slack channel ID
            slack_user_id: Slack user ID
            worktree_path: Path to git worktree
            expires_at: Expiration datetime
            slack_channel_name: Slack channel name (optional)
            slack_user_name: Slack user name (optional)
            slack_thread_link: Slack thread link (optional)
            tmux_session_name: tmux session name (optional)
        """
        now = datetime.now()

        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                INSERT INTO sessions
                (id, slack_thread_id, slack_channel_id, slack_channel_name,
                 slack_user_id, slack_user_name, slack_thread_link,
                 tmux_session_name, worktree_path, status,
                 created_at, last_activity, expires_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
                """,
                (
                    session_id,
                    slack_thread_id,
                    slack_channel_id,
                    slack_channel_name,
                    slack_user_id,
                    slack_user_name,
                    slack_thread_link,
                    tmux_session_name,
                    worktree_path,
                    now,
                    now,
                    expires_at,
                ),
            )

        logger.info(
            "session_created",
            session_id=session_id,
            slack_thread_id=slack_thread_id,
        )

    def get_session_by_slack_thread(self, slack_thread_id: str) -> Optional[dict]:
        """Get session by Slack thread ID.

        Args:
            slack_thread_id: Slack thread ID

        Returns:
            Session dict or None if not found
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.execute(
                """
                SELECT * FROM sessions
                WHERE slack_thread_id = ? AND status = 'active'
                """,
                (slack_thread_id,),
            )
            row = cursor.fetchone()
            return dict(row) if row else None

    def update_last_activity(self, session_id: str) -> None:
        """Update last activity timestamp.

        Args:
            session_id: Session ID
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                UPDATE sessions
                SET last_activity = ?
                WHERE id = ?
                """,
                (datetime.now(), session_id),
            )

    def update_tmux_session_name(
        self,
        session_id: str,
        tmux_session_name: str,
    ) -> None:
        """Update tmux session name.

        Args:
            session_id: Session ID
            tmux_session_name: tmux session name
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                UPDATE sessions
                SET tmux_session_name = ?
                WHERE id = ?
                """,
                (tmux_session_name, session_id),
            )

        logger.debug(
            "tmux_session_name_updated",
            session_id=session_id,
            tmux_session_name=tmux_session_name,
        )

    def update_github_pr(
        self,
        session_id: str,
        branch: str,
        pr_url: str,
        pr_number: int,
    ) -> None:
        """Update GitHub PR information.

        Args:
            session_id: Session ID
            branch: GitHub branch name
            pr_url: PR URL
            pr_number: PR number
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                UPDATE sessions
                SET github_branch = ?, github_pr_url = ?, github_pr_number = ?
                WHERE id = ?
                """,
                (branch, pr_url, pr_number, session_id),
            )

        logger.info(
            "github_pr_updated",
            session_id=session_id,
            pr_url=pr_url,
        )

    def get_active_session_count(self) -> int:
        """Get count of active sessions.

        Returns:
            Number of active sessions
        """
        with sqlite3.connect(self.db_path) as conn:
            cursor = conn.execute(
                """
                SELECT COUNT(*) FROM sessions
                WHERE status = 'active'
                """
            )
            return cursor.fetchone()[0]

    def get_expired_sessions(self) -> list[dict]:
        """Get expired sessions.

        Returns:
            List of expired session dicts
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.row_factory = sqlite3.Row
            cursor = conn.execute(
                """
                SELECT * FROM sessions
                WHERE status = 'active' AND expires_at < ?
                """,
                (datetime.now(),),
            )
            return [dict(row) for row in cursor.fetchall()]

    def mark_session_expired(self, session_id: str) -> None:
        """Mark session as expired.

        Args:
            session_id: Session ID
        """
        with sqlite3.connect(self.db_path) as conn:
            conn.execute(
                """
                UPDATE sessions
                SET status = 'expired'
                WHERE id = ?
                """,
                (session_id,),
            )

        logger.info("session_expired", session_id=session_id)
