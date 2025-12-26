#!/usr/bin/env python3
"""
PHPのENUMファイルから値を抽出するスクリプト

Usage:
    python3 extract_enums.py <enum_file_path>
    python3 extract_enums.py projects/glow-server/api/app/Domain/Mission/Enums/MissionCriterionType.php
"""

import argparse
import json
import re
import sys
from pathlib import Path
from typing import Dict, List


def extract_enum_values(php_file: Path) -> Dict[str, List[str]]:
    """
    PHPのENUMファイルから値を抽出する

    Args:
        php_file: PHPファイルのパス

    Returns:
        {enum_name: [value1, value2, ...]}
    """
    try:
        with open(php_file, 'r', encoding='utf-8') as f:
            content = f.read()
    except Exception as e:
        print(f"Error reading file: {e}", file=sys.stderr)
        return {}

    # enum名を抽出
    enum_name_pattern = r'enum\s+(\w+)\s*:\s*string'
    enum_match = re.search(enum_name_pattern, content)

    if not enum_match:
        print(f"No enum found in {php_file}", file=sys.stderr)
        return {}

    enum_name = enum_match.group(1)

    # case文から値を抽出
    # case CONSTANT_NAME = 'ActualValue';
    case_pattern = r"case\s+\w+\s*=\s*'([^']+)'"
    values = re.findall(case_pattern, content)

    return {enum_name: values}


def main():
    parser = argparse.ArgumentParser(description="PHPのENUMファイルから値を抽出")
    parser.add_argument("enum_file", help="PHPのENUMファイルパス")
    parser.add_argument("--output", "-o", help="出力JSONファイルパス（省略時は標準出力）")

    args = parser.parse_args()

    enum_file = Path(args.enum_file)
    if not enum_file.exists():
        print(f"Error: File not found: {enum_file}", file=sys.stderr)
        sys.exit(1)

    result = extract_enum_values(enum_file)

    if not result:
        print("No enum values found", file=sys.stderr)
        sys.exit(1)

    output_json = json.dumps(result, ensure_ascii=False, indent=2)

    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            f.write(output_json)
        print(f"Enum values saved to {args.output}", file=sys.stderr)
    else:
        print(output_json)


if __name__ == "__main__":
    main()
