#!/usr/bin/env python3
"""
Generate marketplace.json for a Claude Code Plugin Marketplace.

Usage:
    python3 generate_marketplace.py \\
        --name <marketplace-name> \\
        --owner-name "<owner-name>" \\
        --owner-email "<owner-email>" \\
        --plugins '<plugins-json>' \\
        --output <output-path>
"""

import argparse
import json
import sys
from pathlib import Path
from typing import Any, Dict, List, Optional


def validate_marketplace_name(name: str) -> bool:
    """Validate marketplace name follows kebab-case convention."""
    if not name:
        return False
    # Check for kebab-case: lowercase letters, numbers, and hyphens only
    return all(c.islower() or c.isdigit() or c == '-' for c in name)


def validate_plugin_entry(plugin: Dict[str, Any]) -> bool:
    """Validate a single plugin entry has required fields."""
    required_fields = ['name', 'source']
    return all(field in plugin for field in required_fields)


def generate_marketplace_json(
    name: str,
    owner_name: str,
    owner_email: Optional[str] = None,
    plugins: List[Dict[str, Any]] = None,
    metadata: Optional[Dict[str, Any]] = None
) -> Dict[str, Any]:
    """Generate marketplace.json structure."""

    marketplace = {
        "name": name,
        "owner": {
            "name": owner_name
        }
    }

    # Add owner email if provided
    if owner_email:
        marketplace["owner"]["email"] = owner_email

    # Add metadata if provided
    if metadata:
        marketplace["metadata"] = metadata

    # Add plugins
    marketplace["plugins"] = plugins if plugins else []

    return marketplace


def main():
    parser = argparse.ArgumentParser(
        description='Generate marketplace.json for Claude Code Plugin Marketplace'
    )
    parser.add_argument(
        '--name',
        required=True,
        help='Marketplace name (kebab-case)'
    )
    parser.add_argument(
        '--owner-name',
        required=True,
        help='Owner/maintainer name'
    )
    parser.add_argument(
        '--owner-email',
        help='Owner email (optional)'
    )
    parser.add_argument(
        '--plugins',
        default='[]',
        help='Plugins JSON array (default: empty array)'
    )
    parser.add_argument(
        '--metadata',
        help='Metadata JSON object (optional)'
    )
    parser.add_argument(
        '--output',
        required=True,
        help='Output file path for marketplace.json'
    )
    parser.add_argument(
        '--pretty',
        action='store_true',
        help='Pretty-print JSON output'
    )

    args = parser.parse_args()

    # Validate marketplace name
    if not validate_marketplace_name(args.name):
        print(f"❌ Error: Invalid marketplace name '{args.name}'", file=sys.stderr)
        print("   Marketplace name must use kebab-case (lowercase letters, numbers, hyphens only)", file=sys.stderr)
        sys.exit(1)

    # Parse plugins JSON
    try:
        plugins = json.loads(args.plugins)
        if not isinstance(plugins, list):
            raise ValueError("Plugins must be a JSON array")
    except json.JSONDecodeError as e:
        print(f"❌ Error: Invalid plugins JSON: {e}", file=sys.stderr)
        sys.exit(1)
    except ValueError as e:
        print(f"❌ Error: {e}", file=sys.stderr)
        sys.exit(1)

    # Validate each plugin entry
    for i, plugin in enumerate(plugins):
        if not validate_plugin_entry(plugin):
            print(f"❌ Error: Plugin at index {i} missing required fields (name, source)", file=sys.stderr)
            sys.exit(1)

    # Parse metadata JSON if provided
    metadata = None
    if args.metadata:
        try:
            metadata = json.loads(args.metadata)
            if not isinstance(metadata, dict):
                raise ValueError("Metadata must be a JSON object")
        except json.JSONDecodeError as e:
            print(f"❌ Error: Invalid metadata JSON: {e}", file=sys.stderr)
            sys.exit(1)
        except ValueError as e:
            print(f"❌ Error: {e}", file=sys.stderr)
            sys.exit(1)

    # Generate marketplace JSON
    print(f"🚀 Generating marketplace.json for: {args.name}")

    marketplace_json = generate_marketplace_json(
        name=args.name,
        owner_name=args.owner_name,
        owner_email=args.owner_email,
        plugins=plugins,
        metadata=metadata
    )

    # Write to output file
    try:
        output_path = Path(args.output)
        output_path.parent.mkdir(parents=True, exist_ok=True)

        with open(output_path, 'w', encoding='utf-8') as f:
            if args.pretty:
                json.dump(marketplace_json, f, indent=2, ensure_ascii=False)
            else:
                json.dump(marketplace_json, f, ensure_ascii=False)
            f.write('\n')

        print(f"✅ Successfully generated: {output_path}")
        print(f"   Owner: {args.owner_name}")
        print(f"   Plugins: {len(plugins)}")

    except Exception as e:
        print(f"❌ Error writing output file: {e}", file=sys.stderr)
        sys.exit(1)


if __name__ == '__main__':
    main()
