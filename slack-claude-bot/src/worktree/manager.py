"""Git worktree management."""

import json
import subprocess
from pathlib import Path
from typing import Optional

import structlog

logger = structlog.get_logger()


class WorktreeManager:
    """Manage git worktrees for session isolation."""

    def __init__(
        self,
        base_path: Path,
        source_repo: Path,
        default_branch: str = "main",
    ):
        """Initialize worktree manager.

        Args:
            base_path: Base directory for worktrees
            source_repo: Path to source git repository
            default_branch: Default branch to checkout
        """
        self.base_path = base_path
        self.source_repo = source_repo
        self.default_branch = default_branch

        # Ensure base directory exists
        self.base_path.mkdir(parents=True, exist_ok=True)

    def create_worktree(
        self,
        session_id: str,
        branch: Optional[str] = None,
    ) -> Path:
        """Create a new worktree for a session.

        Args:
            session_id: Session identifier
            branch: Branch to checkout (default: main)

        Returns:
            Path to created worktree

        Raises:
            RuntimeError: If worktree creation fails
        """
        worktree_path = self.base_path / f"session_{session_id}"
        branch = branch or self.default_branch

        # Check if worktree already exists
        if worktree_path.exists():
            logger.warning(
                "worktree_already_exists",
                worktree_path=str(worktree_path),
            )
            return worktree_path

        try:
            # Try to create worktree from origin/<branch> (no fetch)
            remote_branch = f"origin/{branch}"
            try:
                result = subprocess.run(
                    [
                        "git",
                        "-C",
                        str(self.source_repo),
                        "worktree",
                        "add",
                        "--detach",
                        str(worktree_path),
                        remote_branch,
                    ],
                    capture_output=True,
                    text=True,
                    check=True,
                )

                logger.info(
                    "worktree_created",
                    session_id=session_id,
                    worktree_path=str(worktree_path),
                    branch=remote_branch,
                    fetched=False,
                )

                return worktree_path

            except subprocess.CalledProcessError as e:
                # origin/<branch> not found locally, fetch and retry
                logger.info(
                    "worktree_creation_failed_will_fetch",
                    session_id=session_id,
                    branch=branch,
                    error=e.stderr,
                )
                self._fetch_branch(branch)

                # Retry
                result = subprocess.run(
                    [
                        "git",
                        "-C",
                        str(self.source_repo),
                        "worktree",
                        "add",
                        "--detach",
                        str(worktree_path),
                        remote_branch,
                    ],
                    capture_output=True,
                    text=True,
                    check=True,
                )

                logger.info(
                    "worktree_created",
                    session_id=session_id,
                    worktree_path=str(worktree_path),
                    branch=remote_branch,
                    fetched=True,
                )

                return worktree_path

        except subprocess.CalledProcessError as e:
            logger.error(
                "worktree_creation_failed",
                session_id=session_id,
                error=e.stderr,
            )
            raise RuntimeError(f"Failed to create worktree: {e.stderr}") from e

    def _fetch_branch(self, branch: str) -> None:
        """Fetch the latest state of the specified branch from remote.

        Args:
            branch: Branch name to fetch
        """
        try:
            subprocess.run(
                [
                    "git",
                    "-C",
                    str(self.source_repo),
                    "fetch",
                    "origin",
                    branch,
                ],
                capture_output=True,
                text=True,
                check=True,
            )
            logger.info("branch_fetched", branch=branch)
        except subprocess.CalledProcessError as e:
            logger.warning("fetch_failed", branch=branch, error=e.stderr)
            raise

    def remove_worktree(self, worktree_path: Path) -> bool:
        """Remove a worktree.

        Args:
            worktree_path: Path to worktree to remove

        Returns:
            True if successful, False otherwise
        """
        if not worktree_path.exists():
            logger.warning(
                "worktree_not_found",
                worktree_path=str(worktree_path),
            )
            return False

        try:
            # Remove worktree
            subprocess.run(
                [
                    "git",
                    "-C",
                    str(self.source_repo),
                    "worktree",
                    "remove",
                    "--force",
                    str(worktree_path),
                ],
                capture_output=True,
                text=True,
                check=True,
            )

            # Manually remove directory if it still exists
            if worktree_path.exists():
                import shutil
                shutil.rmtree(worktree_path)

            # Prune worktree metadata
            self._prune_worktrees()

            logger.info(
                "worktree_removed",
                worktree_path=str(worktree_path),
            )

            return True

        except subprocess.CalledProcessError as e:
            logger.error(
                "worktree_removal_failed",
                worktree_path=str(worktree_path),
                error=e.stderr,
            )
            return False

    def _prune_worktrees(self) -> None:
        """Prune stale worktree metadata."""
        try:
            subprocess.run(
                [
                    "git",
                    "-C",
                    str(self.source_repo),
                    "worktree",
                    "prune",
                ],
                capture_output=True,
                text=True,
                check=True,
            )
        except subprocess.CalledProcessError as e:
            logger.warning("worktree_prune_failed", error=e.stderr)

    def list_worktrees(self) -> list[dict]:
        """List all worktrees.

        Returns:
            List of worktree information dicts
        """
        try:
            result = subprocess.run(
                [
                    "git",
                    "-C",
                    str(self.source_repo),
                    "worktree",
                    "list",
                    "--porcelain",
                ],
                capture_output=True,
                text=True,
                check=True,
            )

            worktrees = []
            current = {}

            for line in result.stdout.split("\n"):
                if line.startswith("worktree "):
                    if current:
                        worktrees.append(current)
                    current = {"path": line.split(" ", 1)[1]}
                elif line.startswith("HEAD "):
                    current["head"] = line.split(" ", 1)[1]
                elif line.startswith("branch "):
                    current["branch"] = line.split(" ", 1)[1]
                elif line == "detached":
                    current["detached"] = True

            if current:
                worktrees.append(current)

            return worktrees

        except subprocess.CalledProcessError as e:
            logger.error("worktree_list_failed", error=e.stderr)
            return []

    def get_worktree_count(self) -> int:
        """Get count of active worktrees (excluding main repo).

        Returns:
            Number of worktrees
        """
        worktrees = self.list_worktrees()
        # Exclude main repository
        return len([w for w in worktrees if str(self.base_path) in w.get("path", "")])

    def get_available_versions(self) -> list[str]:
        """Get list of available versions from config/versions.json.

        Returns:
            List of version names (branches) from versions.json

        Raises:
            FileNotFoundError: If versions.json doesn't exist
            json.JSONDecodeError: If versions.json is invalid
        """
        versions_path = self.source_repo / "config" / "versions.json"

        if not versions_path.exists():
            logger.error(
                "versions_file_not_found",
                path=str(versions_path),
            )
            raise FileNotFoundError(f"versions.json not found at {versions_path}")

        try:
            with open(versions_path, encoding="utf-8") as f:
                data = json.load(f)

            versions = list(data.get("versions", {}).keys())

            logger.info(
                "versions_loaded",
                count=len(versions),
                versions=versions,
            )

            return versions

        except json.JSONDecodeError as e:
            logger.error(
                "versions_json_decode_error",
                path=str(versions_path),
                error=str(e),
            )
            raise

    def get_commit_hash(self, worktree_path: Path, short: bool = True) -> str:
        """Get the current HEAD commit hash of a worktree.

        Args:
            worktree_path: Path to the worktree
            short: If True, return short hash (7 chars)

        Returns:
            Commit hash string

        Raises:
            RuntimeError: If git command fails
        """
        try:
            args = ["git", "-C", str(worktree_path), "rev-parse"]
            if short:
                args.append("--short")
            args.append("HEAD")

            result = subprocess.run(
                args,
                capture_output=True,
                text=True,
                check=True,
            )

            return result.stdout.strip()

        except subprocess.CalledProcessError as e:
            logger.error(
                "get_commit_hash_failed",
                worktree_path=str(worktree_path),
                error=e.stderr,
            )
            raise RuntimeError(f"Failed to get commit hash: {e.stderr}") from e
