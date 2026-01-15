"""Claude Code executor using subprocess."""

import os
import subprocess
import time
from pathlib import Path
from typing import Optional

import structlog

from src.config import Config

logger = structlog.get_logger()


class ClaudeResult:
    """Result from Claude execution."""

    def __init__(
        self,
        output: str,
        is_error: bool = False,
        error_message: Optional[str] = None,
        duration_seconds: float = 0.0,
    ):
        self.output = output
        self.is_error = is_error
        self.error_message = error_message
        self.duration_seconds = duration_seconds


class ClaudeExecutor:
    """Execute Claude Code using subprocess."""

    def __init__(self, timeout_seconds: int = 300):
        """Initialize Claude executor.

        Args:
            timeout_seconds: Command timeout in seconds
        """
        self.timeout_seconds = timeout_seconds

    async def execute(
        self,
        prompt: str,
        worktree_path: Path,
        session_id: str,
        is_first_message: bool = False,
        agent_name: Optional[str] = None,
    ) -> ClaudeResult:
        """Execute Claude prompt directly.

        Args:
            prompt: User prompt
            worktree_path: Working directory (worktree path)
            session_id: Session ID (for logging)
            is_first_message: Whether this is the first message in session
            agent_name: Agent name for Claude CLI --agent option (optional)

        Returns:
            ClaudeResult
        """
        start_time = time.time()

        # Build command
        cmd = [str(Config.CLAUDE_COMMAND_PATH)]

        # Add --agent option if specified
        if agent_name:
            cmd.extend(["--agent", agent_name])

        if not is_first_message:
            cmd.append("-c")  # Continue session
        cmd.extend(["-p", "--dangerously-skip-permissions", prompt])

        logger.info(
            "claude_executing",
            session_id=session_id,
            is_first=is_first_message,
            agent_name=agent_name,
            cmd=" ".join(cmd[:3]) + " <prompt>",
            worktree=str(worktree_path),
        )

        try:
            # Prepare environment variables
            env = os.environ.copy()
            # If API key is empty, remove it from environment (use Max plan auth)
            if not env.get("ANTHROPIC_API_KEY"):
                env.pop("ANTHROPIC_API_KEY", None)

            result = subprocess.run(
                cmd,
                cwd=str(worktree_path),
                capture_output=True,
                text=True,
                timeout=self.timeout_seconds,
                env=env,
            )

            duration = time.time() - start_time

            logger.info(
                "claude_executed",
                session_id=session_id,
                duration=duration,
                returncode=result.returncode,
                output_length=len(result.stdout),
                stderr_length=len(result.stderr),
            )

            return ClaudeResult(
                output=result.stdout,
                is_error=result.returncode != 0,
                error_message=result.stderr if result.returncode != 0 else None,
                duration_seconds=duration,
            )

        except subprocess.TimeoutExpired:
            duration = time.time() - start_time
            error_msg = f"Claude execution timed out after {self.timeout_seconds}s"
            logger.error(
                "claude_timeout",
                session_id=session_id,
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=error_msg,
                duration_seconds=duration,
            )

        except Exception as e:
            duration = time.time() - start_time
            logger.error(
                "claude_execution_failed",
                session_id=session_id,
                error=str(e),
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=str(e),
                duration_seconds=duration,
            )
