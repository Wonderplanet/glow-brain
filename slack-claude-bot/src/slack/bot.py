"""Slack Bot implementation."""

import asyncio
import re

import structlog
from slack_bolt.async_app import AsyncApp
from slack_bolt.adapter.socket_mode.async_handler import AsyncSocketModeHandler

from ..config import Config
from .command_handlers import CommandHandlers
from .handlers import SlackHandlers

logger = structlog.get_logger()


class SlackClaudeBot:
    """Slack-Claude integration bot."""

    def __init__(
        self,
        session_handler,
        command_handlers: CommandHandlers,
    ):
        """Initialize Slack bot.

        Args:
            session_handler: Common session handler
            command_handlers: Command handlers for slash commands
        """
        self.app = AsyncApp(
            token=Config.SLACK_BOT_TOKEN,
            signing_secret=None,  # Not needed in Socket Mode
        )

        self.handlers = SlackHandlers(
            session_handler=session_handler,
            worktree_manager=command_handlers.worktree_manager,
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

        @self.app.action(re.compile("^select_branch_"))
        async def handle_branch_select(ack, body, client):
            """Handle branch selection button click."""
            await self.command_handlers.handle_branch_select_action(ack, body, client)

        @self.app.action(re.compile("^select_agent_"))
        async def handle_agent_select(ack, body, client):
            """Handle agent selection button click."""
            await self.command_handlers.handle_agent_select_action(ack, body, client)

        @self.app.event("message")
        async def handle_message(event, client, say):
            """Handle thread messages."""
            await self.command_handlers.handle_thread_message(event, client, say)

        logger.debug("event_handlers_registered")

    async def start(self) -> None:
        """Start the bot in Socket Mode."""
        handler = AsyncSocketModeHandler(self.app, Config.SLACK_APP_TOKEN)
        await handler.start_async()
        logger.info("slack_bot_started")
