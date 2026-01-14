"""Claude Code executor using headless mode."""

import asyncio
import os
import time
from pathlib import Path
from typing import Optional

import structlog

logger = structlog.get_logger()

# Claude executable path
CLAUDE_PATH = os.path.expanduser("~/.claude/local/claude")


class ClaudeResult:
    """Result from Claude execution."""

    def __init__(
        self,
        output: str,
        is_error: bool = False,
        error_message: Optional[str] = None,
        duration_seconds: float = 0.0,
        claude_session_id: Optional[str] = None,
    ):
        self.output = output
        self.is_error = is_error
        self.error_message = error_message
        self.duration_seconds = duration_seconds
        self.claude_session_id = claude_session_id


class ClaudeExecutor:
    """Execute Claude Code using headless mode."""

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
        is_first_message: bool = True,
    ) -> ClaudeResult:
        """Execute Claude prompt in headless mode.

        Args:
            prompt: User prompt
            worktree_path: Working directory
            session_id: Claude session ID (UUID)
            is_first_message: Whether this is the first message

        Returns:
            ClaudeResult with output
        """
        start_time = time.time()

        cmd = [CLAUDE_PATH, "-p", "--dangerously-skip-permissions"]

        if is_first_message:
            cmd.extend(["--session-id", session_id])
        else:
            cmd.extend(["-r", session_id])

        # Add prompt as argument
        cmd.append(prompt)

        logger.info(
            "claude_execute_start",
            session_id=session_id,
            is_first=is_first_message,
            worktree=str(worktree_path),
        )

        try:
            process = await asyncio.create_subprocess_exec(
                *cmd,
                stdout=asyncio.subprocess.PIPE,
                stderr=asyncio.subprocess.PIPE,
                cwd=str(worktree_path),
            )

            stdout, stderr = await asyncio.wait_for(
                process.communicate(),
                timeout=self.timeout_seconds,
            )

            duration = time.time() - start_time
            output = stdout.decode()
            error_output = stderr.decode()

            if process.returncode != 0:
                # Include both stdout and stderr in error message
                error_msg = error_output or output or f"Exit code: {process.returncode}"
                logger.error(
                    "claude_execute_failed",
                    session_id=session_id,
                    returncode=process.returncode,
                    stderr=error_output[:500],
                    stdout=output[:500],
                    duration=duration,
                )
                return ClaudeResult(
                    output="",
                    is_error=True,
                    error_message=error_msg,
                    duration_seconds=duration,
                    claude_session_id=session_id,
                )

            logger.info(
                "claude_execute_success",
                session_id=session_id,
                output_length=len(output),
                duration=duration,
            )

            return ClaudeResult(
                output=output,
                is_error=False,
                duration_seconds=duration,
                claude_session_id=session_id,
            )

        except asyncio.TimeoutError:
            duration = time.time() - start_time
            logger.error(
                "claude_execute_timeout",
                session_id=session_id,
                timeout=self.timeout_seconds,
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=f"Timeout after {self.timeout_seconds}s",
                duration_seconds=duration,
                claude_session_id=session_id,
            )

        except Exception as e:
            duration = time.time() - start_time
            logger.error(
                "claude_execute_error",
                session_id=session_id,
                error=str(e),
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=str(e),
                duration_seconds=duration,
                claude_session_id=session_id,
            )
