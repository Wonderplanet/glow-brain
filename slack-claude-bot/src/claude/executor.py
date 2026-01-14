"""Claude Code executor using tmux + pexpect."""

import os
import re
import time
from pathlib import Path
from typing import Optional

import pexpect
import structlog

logger = structlog.get_logger()


class ClaudeResult:
    """Result from Claude execution."""

    def __init__(
        self,
        output: str,
        is_error: bool = False,
        error_message: Optional[str] = None,
        duration_seconds: float = 0.0,
        tmux_session_name: Optional[str] = None,
    ):
        self.output = output
        self.is_error = is_error
        self.error_message = error_message
        self.duration_seconds = duration_seconds
        self.tmux_session_name = tmux_session_name


class ClaudeExecutor:
    """Execute Claude Code using pexpect."""

    # ANSI escape sequence pattern
    ANSI_ESCAPE = re.compile(r'\x1B(?:[@-Z\\-_]|\[[0-?]*[ -/]*[@-~])')

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
        tmux_session_name: Optional[str],
        session_id: str,
        is_first_message: bool = False,
    ) -> ClaudeResult:
        """Execute Claude prompt in tmux session.

        Args:
            prompt: User prompt
            worktree_path: Working directory (worktree path)
            tmux_session_name: tmux session name (None for first message)
            session_id: Session ID (used to generate tmux session name)
            is_first_message: Whether this is the first message in session

        Returns:
            ClaudeResult with tmux_session_name set
        """
        start_time = time.time()

        try:
            # Generate tmux session name if first message
            if is_first_message:
                tmux_session_name = f"claude_{session_id}"

                # Create tmux session and start claude
                await self._start_claude_session(
                    tmux_session_name,
                    worktree_path,
                )

            # Send prompt and get response
            output = await self._send_prompt_and_wait(
                tmux_session_name,
                prompt,
            )

            duration = time.time() - start_time

            logger.info(
                "claude_executed",
                tmux_session=tmux_session_name,
                duration=duration,
                output_length=len(output),
            )

            result = ClaudeResult(
                output=output,
                is_error=False,
                duration_seconds=duration,
            )
            result.tmux_session_name = tmux_session_name
            return result

        except pexpect.TIMEOUT:
            duration = time.time() - start_time
            error_msg = f"Claude execution timed out after {self.timeout_seconds}s"
            logger.error(
                "claude_timeout",
                tmux_session=tmux_session_name,
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=error_msg,
                duration_seconds=duration,
                tmux_session_name=tmux_session_name,
            )

        except Exception as e:
            duration = time.time() - start_time
            logger.error(
                "claude_execution_failed",
                tmux_session=tmux_session_name,
                error=str(e),
                duration=duration,
            )
            return ClaudeResult(
                output="",
                is_error=True,
                error_message=str(e),
                duration_seconds=duration,
                tmux_session_name=tmux_session_name,
            )

    async def _start_claude_session(
        self,
        tmux_session_name: str,
        worktree_path: Path,
    ) -> None:
        """Start Claude in a new tmux session.

        Args:
            tmux_session_name: tmux session name
            worktree_path: Working directory
        """
        import subprocess

        # Check if tmux session already exists
        result = subprocess.run(
            ["tmux", "has-session", "-t", tmux_session_name],
            capture_output=True,
        )

        if result.returncode == 0:
            logger.info("tmux_session_already_exists", session=tmux_session_name)
            return

        # Create new tmux session
        subprocess.run(
            [
                "tmux",
                "new-session",
                "-d",
                "-s",
                tmux_session_name,
                "-c",
                str(worktree_path),
            ],
            check=True,
        )

        # Start claude in the session
        subprocess.run(
            [
                "tmux",
                "send-keys",
                "-t",
                tmux_session_name,
                "claude",
                "Enter",
            ],
            check=True,
        )

        # Wait for Claude to start (wait for prompt)
        import asyncio
        await asyncio.sleep(3)

        logger.info(
            "claude_session_started",
            tmux_session=tmux_session_name,
            worktree=str(worktree_path),
        )

    async def _send_prompt_and_wait(
        self,
        tmux_session_name: str,
        prompt: str,
    ) -> str:
        """Send prompt to tmux session and wait for response.

        Args:
            tmux_session_name: tmux session name
            prompt: User prompt

        Returns:
            Claude's response (cleaned)
        """
        import subprocess
        import asyncio

        # Escape special characters in prompt
        escaped_prompt = prompt.replace("'", "'\\''")

        # Send prompt
        subprocess.run(
            [
                "tmux",
                "send-keys",
                "-t",
                tmux_session_name,
                escaped_prompt,
                "Enter",
            ],
            check=True,
        )

        logger.debug("prompt_sent", tmux_session=tmux_session_name)

        # Wait for response (poll tmux pane content)
        max_wait = self.timeout_seconds
        poll_interval = 2
        waited = 0

        previous_content = ""

        while waited < max_wait:
            await asyncio.sleep(poll_interval)
            waited += poll_interval

            # Capture pane content
            result = subprocess.run(
                [
                    "tmux",
                    "capture-pane",
                    "-t",
                    tmux_session_name,
                    "-p",
                ],
                capture_output=True,
                text=True,
            )

            current_content = result.stdout

            # Check if content has changed
            if current_content == previous_content:
                # Content hasn't changed for poll_interval seconds
                # Assume response is complete
                break

            previous_content = current_content

        # Extract response
        response = self._extract_response(current_content, prompt)

        return response

    def _extract_response(self, pane_content: str, prompt: str) -> str:
        """Extract Claude's response from tmux pane content.

        Args:
            pane_content: Full pane content
            prompt: Original prompt

        Returns:
            Extracted and cleaned response
        """
        # Remove ANSI escape sequences
        cleaned = self.ANSI_ESCAPE.sub('', pane_content)

        # Try to find the response after the prompt
        lines = cleaned.split('\n')

        # Find where the prompt appears
        response_lines = []
        found_prompt = False

        for line in lines:
            if not found_prompt:
                if prompt in line:
                    found_prompt = True
                continue

            # Skip empty lines immediately after prompt
            if not response_lines and not line.strip():
                continue

            response_lines.append(line)

        # Join and clean up
        response = '\n'.join(response_lines).strip()

        return response

    def kill_session(self, tmux_session_name: str) -> None:
        """Kill tmux session.

        Args:
            tmux_session_name: tmux session name
        """
        import subprocess

        try:
            subprocess.run(
                ["tmux", "kill-session", "-t", tmux_session_name],
                capture_output=True,
                check=True,
            )
            logger.info("tmux_session_killed", session=tmux_session_name)
        except subprocess.CalledProcessError as e:
            logger.warning(
                "tmux_kill_failed",
                session=tmux_session_name,
                error=e.stderr,
            )
