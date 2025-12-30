#!/usr/bin/env python3
"""
Initialize a Claude Code Plugin Marketplace directory structure.

Usage:
    python3 init_marketplace.py <marketplace-name> --path <output-directory>
"""

import argparse
import json
import os
import sys
from pathlib import Path


def create_directory_structure(marketplace_name: str, output_path: str) -> Path:
    """Create the basic marketplace directory structure."""
    marketplace_dir = Path(output_path) / marketplace_name

    # Create main directories
    (marketplace_dir / ".claude-plugin").mkdir(parents=True, exist_ok=True)
    (marketplace_dir / "plugins").mkdir(parents=True, exist_ok=True)

    # Create .gitkeep in plugins directory
    (marketplace_dir / "plugins" / ".gitkeep").touch()

    return marketplace_dir


def create_initial_marketplace_json(marketplace_dir: Path, marketplace_name: str):
    """Create an initial marketplace.json file."""
    marketplace_json = {
        "name": marketplace_name,
        "owner": {
            "name": "TODO: Enter your team or organization name",
            "email": "TODO: Enter your email (optional)"
        },
        "metadata": {
            "description": "TODO: Enter marketplace description",
            "version": "1.0.0",
            "pluginRoot": "./plugins"
        },
        "plugins": []
    }

    marketplace_file = marketplace_dir / ".claude-plugin" / "marketplace.json"
    with open(marketplace_file, 'w', encoding='utf-8') as f:
        json.dump(marketplace_json, f, indent=2, ensure_ascii=False)
        f.write('\n')


def create_readme(marketplace_dir: Path, marketplace_name: str):
    """Create a README.md file."""
    readme_content = f"""# {marketplace_name}

Claude Code Plugin Marketplace

## Overview

This is a Claude Code Plugin Marketplace containing custom plugins for your team or organization.

## Installation

To add this marketplace to your Claude Code installation:

```bash
/plugin marketplace add <repository-url-or-path>
```

## Available Plugins

<!-- List your plugins here -->

## Usage

After adding the marketplace, install individual plugins:

```bash
/plugin install <plugin-name>@{marketplace_name}
```

## Contributing

<!-- Add contribution guidelines here -->

## License

<!-- Specify license here -->
"""

    readme_file = marketplace_dir / "README.md"
    with open(readme_file, 'w', encoding='utf-8') as f:
        f.write(readme_content)


def create_gitignore(marketplace_dir: Path):
    """Create a .gitignore file."""
    gitignore_content = """# OS files
.DS_Store
Thumbs.db

# Editor files
.vscode/
.idea/
*.swp
*.swo
*~

# Python
__pycache__/
*.py[cod]
*$py.class
.Python
*.so

# Node
node_modules/
npm-debug.log*
yarn-debug.log*
yarn-error.log*

# Temporary files
*.tmp
*.bak
*.log
"""

    gitignore_file = marketplace_dir / ".gitignore"
    with open(gitignore_file, 'w', encoding='utf-8') as f:
        f.write(gitignore_content)


def main():
    parser = argparse.ArgumentParser(
        description='Initialize a Claude Code Plugin Marketplace directory structure'
    )
    parser.add_argument(
        'marketplace_name',
        help='Name of the marketplace (kebab-case recommended)'
    )
    parser.add_argument(
        '--path',
        default='.',
        help='Output directory path (default: current directory)'
    )

    args = parser.parse_args()

    # Validate marketplace name
    if not args.marketplace_name:
        print("Error: Marketplace name cannot be empty", file=sys.stderr)
        sys.exit(1)

    # Create directory structure
    print(f"🚀 Initializing marketplace: {args.marketplace_name}")
    print(f"   Location: {args.path}")

    try:
        marketplace_dir = create_directory_structure(args.marketplace_name, args.path)
        print(f"✅ Created marketplace directory: {marketplace_dir}")

        create_initial_marketplace_json(marketplace_dir, args.marketplace_name)
        print(f"✅ Created .claude-plugin/marketplace.json")

        create_readme(marketplace_dir, args.marketplace_name)
        print(f"✅ Created README.md")

        create_gitignore(marketplace_dir)
        print(f"✅ Created .gitignore")

        print(f"\n✅ Marketplace '{args.marketplace_name}' initialized successfully!")
        print(f"\nNext steps:")
        print(f"1. Edit .claude-plugin/marketplace.json to configure owner and metadata")
        print(f"2. Add plugins to the marketplace")
        print(f"3. Run validation: cd {marketplace_dir} && claude plugin validate .")

    except Exception as e:
        print(f"❌ Error: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
