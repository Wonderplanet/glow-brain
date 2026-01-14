"""Slack-Claude Bot entry point."""

import asyncio
import sys

import structlog

from .claude.executor import ClaudeExecutor
from .config import Config
from .github.pr_manager import GitHubPRManager
from .session.db import SessionDB
from .session.manager import SessionManager
from .slack.bot import SlackClaudeBot
from .slack.command_handlers import CommandHandlers
from .worktree.manager import WorktreeManager


def configure_logging():
    """Configure structured logging."""
    structlog.configure(
        processors=[
            structlog.contextvars.merge_contextvars,
            structlog.processors.add_log_level,
            structlog.processors.TimeStamper(fmt="iso"),
            structlog.processors.StackInfoRenderer(),
            structlog.processors.format_exc_info,
            structlog.dev.ConsoleRenderer(),
        ],
        wrapper_class=structlog.make_filtering_bound_logger(Config.LOG_LEVEL),
        context_class=dict,
        logger_factory=structlog.PrintLoggerFactory(),
        cache_logger_on_first_use=True,
    )


async def main():
    """Main entry point."""
    # Configure logging
    configure_logging()
    logger = structlog.get_logger()

    logger.info("slack_claude_bot_starting")

    try:
        # Initialize components
        logger.info("initializing_components")

        # Database
        db = SessionDB(Config.DB_PATH)

        # Worktree manager
        worktree_manager = WorktreeManager(
            base_path=Config.WORKTREE_BASE_PATH,
            source_repo=Config.SOURCE_REPO_PATH,
            default_branch="main",
        )

        # Session manager
        session_manager = SessionManager(
            db=db,
            worktree_manager=worktree_manager,
            max_sessions=Config.MAX_CONCURRENT_SESSIONS,
            ttl_hours=Config.SESSION_TTL_HOURS,
        )

        # Claude executor
        claude_executor = ClaudeExecutor(
            timeout_seconds=Config.CLAUDE_TIMEOUT_SECONDS,
        )

        # GitHub PR manager
        github_manager = GitHubPRManager()

        # Command handlers
        command_handlers = CommandHandlers(
            session_manager=session_manager,
            claude_executor=claude_executor,
            github_manager=github_manager,
            worktree_manager=worktree_manager,
        )

        # Slack bot
        bot = SlackClaudeBot(
            session_manager=session_manager,
            claude_executor=claude_executor,
            github_manager=github_manager,
            command_handlers=command_handlers,
        )

        logger.info("components_initialized")
        logger.info(
            "configuration",
            max_sessions=Config.MAX_CONCURRENT_SESSIONS,
            ttl_hours=Config.SESSION_TTL_HOURS,
            worktree_base=str(Config.WORKTREE_BASE_PATH),
            source_repo=str(Config.SOURCE_REPO_PATH),
        )

        # Start bot
        logger.info("starting_bot")
        await bot.start()

    except KeyboardInterrupt:
        logger.info("received_keyboard_interrupt")
    except Exception as e:
        logger.error("fatal_error", error=str(e), exc_info=True)
        sys.exit(1)


if __name__ == "__main__":
    asyncio.run(main())
