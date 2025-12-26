#!/usr/bin/env python3
"""
コンテンツタイプ識別スクリプト

要件ファイル構成.mdを読み込み、含まれるコンテンツタイプを識別します。
"""

import argparse
import json
import re
import sys
from pathlib import Path


def load_patterns(patterns_file: Path) -> dict:
    """パターン設定ファイルを読み込む"""
    with open(patterns_file, 'r', encoding='utf-8') as f:
        return json.load(f)


def identify_content_types(requirements_doc: str, patterns: dict) -> list[str]:
    """
    要件ファイル構成ドキュメントからコンテンツタイプを識別

    Args:
        requirements_doc: 要件ファイル構成.mdの内容
        patterns: コンテンツタイプパターン辞書

    Returns:
        識別されたコンテンツタイプのリスト
    """
    identified_types = []

    for content_type, config in patterns['patterns'].items():
        for keyword_pattern in config['keywords']:
            if re.search(keyword_pattern, requirements_doc):
                if content_type not in identified_types:
                    identified_types.append(content_type)
                break

    return identified_types


def main():
    parser = argparse.ArgumentParser(
        description='要件ファイル構成からコンテンツタイプを識別'
    )
    parser.add_argument(
        'requirements_file',
        type=Path,
        help='要件ファイル構成.mdのパス'
    )
    parser.add_argument(
        '--patterns',
        type=Path,
        default=Path(__file__).parent / 'content_type_patterns.json',
        help='パターン設定ファイルのパス'
    )
    parser.add_argument(
        '--output',
        choices=['json', 'text'],
        default='json',
        help='出力形式'
    )

    args = parser.parse_args()

    # パターン設定を読み込む
    if not args.patterns.exists():
        print(f"エラー: パターンファイルが見つかりません: {args.patterns}", file=sys.stderr)
        sys.exit(1)

    patterns = load_patterns(args.patterns)

    # 要件ファイル構成を読み込む
    if not args.requirements_file.exists():
        print(f"エラー: 要件ファイルが見つかりません: {args.requirements_file}", file=sys.stderr)
        sys.exit(1)

    with open(args.requirements_file, 'r', encoding='utf-8') as f:
        requirements_doc = f.read()

    # コンテンツタイプを識別
    content_types = identify_content_types(requirements_doc, patterns)

    # 結果を出力
    if args.output == 'json':
        print(json.dumps({
            'content_types': content_types,
            'count': len(content_types)
        }, ensure_ascii=False, indent=2))
    else:
        if content_types:
            print(f"識別されたコンテンツタイプ: {', '.join(content_types)}")
            print(f"合計: {len(content_types)}件")
        else:
            print("コンテンツタイプが識別されませんでした")

    # コンテンツタイプが見つからない場合は警告として終了コード1を返す
    sys.exit(0 if content_types else 1)


if __name__ == '__main__':
    main()
