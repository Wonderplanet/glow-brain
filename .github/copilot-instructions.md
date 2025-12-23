# glow-brain AI Agent Instructions

## What is this project?

**glow-brain** is a **read-only reference workspace** for browsing multiple versions of the GLOW game project. It orchestrates three separate repositories (glow-server, glow-masterdata, glow-client) via version-controlled checkout, enabling cross-version code inspection without modifying any code.

## Critical: This is a Read-Only Repository

**DO NOT attempt to modify code in `projects/` directories.**

- All `git commit`, `git push`, `git merge`, and `git rebase` operations are **blocked by Git hooks** ([scripts/hooks/](../scripts/hooks/))
- The setup script enforces uncommitted change detection before updates
- Changes must be made in the original repositories:
  - `git@github.com:Wonderplanet/glow-server.git`
  - `git@github.com:Wonderplanet/glow-client.git`
  - `git@github.com:Wonderplanet/glow-masterdata.git`

## Architecture Overview

### Repository Structure
```
glow-brain/
├── config/versions.json       # Version-to-branch mappings
├── scripts/
│   ├── setup.sh               # Main orchestration script
│   └── hooks/                 # Git hooks to prevent modifications
└── projects/                  # Managed repositories (DO NOT EDIT)
    ├── glow-server/           # Server code (full clone)
    ├── glow-masterdata/       # Master data CSVs (full clone)
    └── glow-client/           # Unity client (sparse checkout)
```

### Version Management Model

Version configuration in [config/versions.json](../config/versions.json):
- Maps semantic versions (`1.4.1`, `1.5.0`) to specific branch names per repository
- Tracks `current_version` to maintain workspace state
- Each repository may target different branch naming conventions (e.g., `develop/v1.4.1` vs `release/v1.4.1`)

### Client Repository Optimization

**glow-client uses aggressive size reduction**:
- Sparse checkout limited to `Assets/GLOW/Scripts` and `Assets/Framework/Scripts`
- Git LFS is skipped (`GIT_LFS_SKIP_SMUDGE=1`)
- Shallow clone with `--depth 1` and `--filter=blob:none`
- Results in ~100MB vs full repository size

## Essential Workflows

### Setup/Version Switching

```bash
# Setup default version (from config/versions.json)
./scripts/setup.sh

# Switch to specific version
./scripts/setup.sh 1.5.0
```

**What happens:**
1. Checks for `jq` prerequisite (macOS: `brew install jq`)
2. Validates version exists in `config/versions.json`
3. For each repository:
   - Clones if missing (with client-specific optimizations)
   - Updates to target branch if existing (force-resets if diverged)
   - Installs protective Git hooks
4. Updates `current_version` in config

### Recovering from Accidental Changes

```bash
cd projects/glow-server
git reset --hard HEAD
git clean -fd
```

### Adding New Versions

Edit [config/versions.json](../config/versions.json):
```json
{
  "versions": {
    "1.6.0": {
      "glow-server": "develop/v1.6.0",
      "glow-client": "release/v1.6.0",
      "glow-masterdata": "release/dev-ld"
    }
  }
}
```

## Project-Specific Conventions

### Bash Scripting Patterns

[scripts/setup.sh](../scripts/setup.sh) demonstrates the project's shell conventions:
- Uses `set -euo pipefail` for strict error handling
- Color-coded logging functions (`info`, `success`, `error`, `warning`)
- `readonly` variables for paths (`PROJECT_ROOT`, `CONFIG_FILE`)
- Defensive `jq` usage with null checks for JSON parsing

### Force-Push Handling

The update logic ([setup.sh:205-228](../scripts/setup.sh#L205-L228)) handles upstream force-pushes:
```bash
if ! git merge --ff-only "origin/${target_branch}" 2>/dev/null; then
    warning "履歴が分岐しています。リモートに強制的に合わせます..."
    git reset --hard "origin/${target_branch}"
fi
```
This is intentional for read-only mode—always match remote state.

### Git Hook Protection

Hooks in [scripts/hooks/](../scripts/hooks/) provide multi-layered protection:
- `pre-commit`: Blocks commits with Japanese error messages
- `pre-push`: Prevents pushes
- `pre-merge-commit`: Stops merge operations

## Common Operations (DO and DON'T)

### ✅ DO
- Browse code in `projects/` with any editor/IDE
- Search across versions for code evolution
- Compare implementations between versions
- Update to latest code: `./scripts/setup.sh`

### ❌ DON'T
- Create branches in `projects/` subdirectories
- Commit changes (hooks will block, but avoid triggering)
- Add new files to managed repositories
- Assume standard git workflows work (this is not a normal workspace)

## Key Files to Understand

- [config/versions.json](../config/versions.json): Single source of truth for version mappings
- [scripts/setup.sh](../scripts/setup.sh): All orchestration logic (397 lines)
- [README.md](../README.md): User-facing documentation in Japanese
- [scripts/hooks/pre-commit](../scripts/hooks/pre-commit): Example protection mechanism

## Troubleshooting

**"jq コマンドが見つかりません"**
- Install: `brew install jq` (macOS)

**"未コミットの変更があります"**
- Reset: `cd projects/<repo> && git reset --hard HEAD && git clean -fd`

**Wrong version checked out**
- Re-run: `./scripts/setup.sh <version>` (idempotent operation)

**Client repository too large**
- Expected behavior if LFS assets were pulled
- Delete and re-setup: `rm -rf projects/glow-client && ./scripts/setup.sh`
