#!/usr/bin/env python3
"""
Generate plugin.json for a Claude Code Plugin.

Usage:
    python3 generate_plugin.py \\
        --name <plugin-name> \\
        --description "<description>" \\
        --version <version> \\
        --output <output-path>
"""

import argparse
import json
import sys
from pathlib import Path
from typing import Any, Dict, Optional


def validate_plugin_name(name: str) -> bool:
    """Validate plugin name follows kebab-case convention."""
    if not name:
        return False
    # Check for kebab-case: lowercase letters, numbers, and hyphens only
    return all(c.islower() or c.isdigit() or c == '-' for c in name)


def generate_plugin_json(
    name: str,
    description: str,
    version: str,
    author_name: Optional[str] = None,
    author_email: Optional[str] = None,
    homepage: Optional[str] = None,
    repository: Optional[str] = None,
    license_: Optional[str] = None,
    keywords: Optional[list] = None,
    category: Optional[str] = None
) -> Dict[str, Any]:
    """Generate plugin.json structure."""

    plugin = {
        "name": name,
        "description": description,
        "version": version
    }

    # Add author information if provided
    if author_name or author_email:
        plugin["author"] = {}
        if author_name:
            plugin["author"]["name"] = author_name
        if author_email:
            plugin["author"]["email"] = author_email

    # Add optional fields
    if homepage:
        plugin["homepage"] = homepage
    if repository:
        plugin["repository"] = repository
    if license_:
        plugin["license"] = license_
    if keywords:
        plugin["keywords"] = keywords
    if category:
        plugin["category"] = category

    return plugin


def main():
    parser = argparse.ArgumentParser(
        description='Generate plugin.json for Claude Code Plugin'
    )
    parser.add_argument(
        '--name',
        required=True,
        help='Plugin name (kebab-case)'
    )
    parser.add_argument(
        '--description',
        required=True,
        help='Plugin description'
    )
    parser.add_argument(
        '--version',
        required=True,
        help='Plugin version (semantic versioning, e.g., 1.0.0)'
    )
    parser.add_argument(
        '--author-name',
        help='Author name (optional)'
    )
    parser.add_argument(
        '--author-email',
        help='Author email (optional)'
    )
    parser.add_argument(
        '--homepage',
        help='Homepage URL (optional)'
    )
    parser.add_argument(
        '--repository',
        help='Repository URL (optional)'
    )
    parser.add_argument(
        '--license',
        help='License (e.g., MIT, Apache-2.0) (optional)'
    )
    parser.add_argument(
        '--keywords',
        help='Keywords as comma-separated values (optional)'
    )
    parser.add_argument(
        '--category',
        help='Category (e.g., productivity, devops) (optional)'
    )
    parser.add_argument(
        '--output',
        required=True,
        help='Output file path for plugin.json'
    )
    parser.add_argument(
        '--pretty',
        action='store_true',
        help='Pretty-print JSON output'
    )

    args = parser.parse_args()

    # Validate plugin name
    if not validate_plugin_name(args.name):
        print(f"❌ Error: Invalid plugin name '{args.name}'", file=sys.stderr)
        print("   Plugin name must use kebab-case (lowercase letters, numbers, hyphens only)", file=sys.stderr)
        sys.exit(1)

    # Parse keywords if provided
    keywords = None
    if args.keywords:
        keywords = [kw.strip() for kw in args.keywords.split(',') if kw.strip()]

    # Generate plugin JSON
    print(f"🚀 Generating plugin.json for: {args.name}")

    plugin_json = generate_plugin_json(
        name=args.name,
        description=args.description,
        version=args.version,
        author_name=args.author_name,
        author_email=args.author_email,
        homepage=args.homepage,
        repository=args.repository,
        license_=args.license,
        keywords=keywords,
        category=args.category
    )

    # Write to output file
    try:
        output_path = Path(args.output)
        output_path.parent.mkdir(parents=True, exist_ok=True)

        with open(output_path, 'w', encoding='utf-8') as f:
            if args.pretty:
                json.dump(plugin_json, f, indent=2, ensure_ascii=False)
            else:
                json.dump(plugin_json, f, ensure_ascii=False)
            f.write('\n')

        print(f"✅ Successfully generated: {output_path}")
        print(f"   Version: {args.version}")
        if args.author_name:
            print(f"   Author: {args.author_name}")

    except Exception as e:
        print(f"❌ Error writing output file: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
