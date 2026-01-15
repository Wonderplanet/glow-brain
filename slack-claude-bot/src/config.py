"""Configuration management for Slack-Claude Bot."""

import json
import os
from pathlib import Path
from typing import Optional

import structlog
from dotenv import load_dotenv

logger = structlog.get_logger()

# Load .env file
load_dotenv()


class Config:
    """Application configuration."""

    # Slack
    SLACK_BOT_TOKEN: str = os.getenv("SLACK_BOT_TOKEN", "")
    SLACK_APP_TOKEN: str = os.getenv("SLACK_APP_TOKEN", "")
    SLACK_WORKSPACE_URL: str = os.getenv("SLACK_WORKSPACE_URL", "")

    # Anthropic
    ANTHROPIC_API_KEY: str = os.getenv("ANTHROPIC_API_KEY", "")

    # GitHub
    GITHUB_TOKEN: Optional[str] = os.getenv("GITHUB_TOKEN")
    GITHUB_REPO_OWNER: str = os.getenv("GITHUB_REPO_OWNER", "")
    GITHUB_REPO_NAME: str = os.getenv("GITHUB_REPO_NAME", "glow-brain")
    GITHUB_BASE_BRANCH: str = os.getenv("GITHUB_BASE_BRANCH", "main")

    # Application Settings
    MAX_CONCURRENT_SESSIONS: int = int(os.getenv("MAX_CONCURRENT_SESSIONS", "3"))
    SESSION_TTL_HOURS: int = int(os.getenv("SESSION_TTL_HOURS", "8"))
    CLAUDE_TIMEOUT_SECONDS: int = int(os.getenv("CLAUDE_TIMEOUT_SECONDS", "300"))

    # Claude CLI
    CLAUDE_COMMAND_PATH: Path = Path(os.getenv("CLAUDE_COMMAND_PATH", "~/.claude/local/claude")).expanduser()

    # Paths
    WORKTREE_BASE_PATH: Path = Path(os.getenv("WORKTREE_BASE_PATH", "~/glow-worktrees")).expanduser()
    SOURCE_REPO_PATH: Path = Path(os.getenv("SOURCE_REPO_PATH", "~/Documents/workspace/glow/glow-brain")).expanduser()
    DB_PATH: Path = Path(os.getenv("DB_PATH", "./data/sessions.db"))
    AGENTS_CONFIG_PATH: Path = Path(os.getenv("AGENTS_CONFIG_PATH", "./config/agents.json"))

    # Logging
    LOG_LEVEL: str = os.getenv("LOG_LEVEL", "INFO")

    @classmethod
    def validate(cls) -> None:
        """Validate required configuration values."""
        required_fields = {
            "SLACK_BOT_TOKEN": cls.SLACK_BOT_TOKEN,
            "SLACK_APP_TOKEN": cls.SLACK_APP_TOKEN,
        }

        missing_fields = [
            field for field, value in required_fields.items() if not value
        ]

        if missing_fields:
            raise ValueError(
                f"Missing required configuration: {', '.join(missing_fields)}\n"
                "Please set these environment variables or create a .env file."
            )

    @classmethod
    def get_slack_thread_link(cls, channel_id: str, thread_ts: str) -> str:
        """Generate Slack thread link."""
        # Remove dots from thread_ts for URL
        ts_without_dots = thread_ts.replace(".", "")
        return f"{cls.SLACK_WORKSPACE_URL}/archives/{channel_id}/p{ts_without_dots}"


def get_available_agents() -> list[dict]:
    """Get list of available agents from config/agents.json.

    Returns:
        List of agent configurations (enabled only).
        Returns empty list if file not found or parse error.
    """
    agents_path = Config.AGENTS_CONFIG_PATH

    if not agents_path.exists():
        logger.warning("agents_file_not_found", path=str(agents_path))
        return []

    try:
        with open(agents_path, encoding="utf-8") as f:
            data = json.load(f)

        agents = [
            agent for agent in data.get("agents", {}).values()
            if agent.get("enabled", True)
        ]

        logger.info("agents_loaded", count=len(agents))
        return agents

    except json.JSONDecodeError as e:
        logger.error("agents_json_decode_error", error=str(e))
        return []
    except Exception as e:
        logger.error("agents_load_error", error=str(e))
        return []


# Validate configuration on module import
Config.validate()
