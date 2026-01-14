"""Slack Bot implementation."""

import structlog
from slack_bolt.async_app import AsyncApp
from slack_bolt.adapter.socket_mode.async_handler import AsyncSocketModeHandler

from ..claude.executor import ClaudeExecutor
from ..config import Config
from ..github.pr_manager import GitHubPRManager
from ..session.manager import SessionManager
from .command_handlers import CommandHandlers
from .handlers import SlackHandlers

logger = structlog.get_logger()


class SlackClaudeBot:
    """Slack-Claude integration bot."""

    def __init__(
        self,
        session_manager: SessionManager,
        claude_executor: ClaudeExecutor,
        github_manager: GitHubPRManager,
        command_handlers: CommandHandlers,
    ):
        """Initialize Slack bot.

        Args:
            session_manager: Session manager
            claude_executor: Claude executor
            github_manager: GitHub PR manager
            command_handlers: Command handlers for slash commands
        """
        self.app = AsyncApp(
            token=Config.SLACK_BOT_TOKEN,
            signing_secret=None,  # Not needed in Socket Mode
        )

        self.handlers = SlackHandlers(
            session_manager=session_manager,
            claude_executor=claude_executor,
            github_manager=github_manager,
        )

        self.command_handlers = command_handlers

        # Register event handlers
        self._register_handlers()

        logger.info("slack_bot_initialized")

    def _register_handlers(self) -> None:
        """Register Slack event handlers."""

        @self.app.event("app_mention")
        async def handle_mention(event, client, say):
            """Handle app mention events."""
            await self.handlers.handle_app_mention(event, client, say)

        @self.app.command("/mst-input-guide")
        async def handle_glow_brain_command(ack, body, client):
            """Handle /mst-input-guide slash command."""
            await self.command_handlers.handle_glow_brain_command(ack, body, client)

        @self.app.view("glow_brain_modal")
        async def handle_modal_submission(ack, body, client, view):
            """Handle modal submission."""
            await self.command_handlers.handle_modal_submission(ack, body, client, view)

        logger.debug("event_handlers_registered")

    async def start(self) -> None:
        """Start the bot in Socket Mode."""
        handler = AsyncSocketModeHandler(self.app, Config.SLACK_APP_TOKEN)
        await handler.start_async()
        logger.info("slack_bot_started")
