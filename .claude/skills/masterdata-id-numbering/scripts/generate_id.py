#!/usr/bin/env python3
"""
GLOWマスタデータのID生成スクリプト

ID割り振りルール.csvから採番ルールを読み取り、指定されたパラメータに基づいてIDを生成します。
"""

import csv
import sys
import os
import re
from pathlib import Path


def get_csv_path():
    """ID割り振りルール.csvのパスを取得"""
    script_dir = Path(__file__).parent
    project_root = script_dir.parent.parent.parent.parent
    csv_path = project_root / "domain/raw-data/google-drive/spread-sheet/GLOW/010_企画・仕様/GLOW_ID 管理/ID割り振りルール.csv"

    if not csv_path.exists():
        print(f"エラー: ID割り振りルール.csvが見つかりません", file=sys.stderr)
        print(f"パス: {csv_path}", file=sys.stderr)
        sys.exit(1)

    return csv_path


def load_numbering_rules():
    """ID割り振りルール.csvから採番ルールを読み込む"""
    csv_path = get_csv_path()
    rules = {}

    with open(csv_path, 'r', encoding='utf-8') as f:
        reader = csv.reader(f)
        for i, row in enumerate(reader):
            # 7行目以降（データ行）
            if i < 6 or len(row) < 5:
                continue

            category = row[2].strip()
            rule_desc = row[3].strip()
            max_digits = row[4].strip()

            if not category:
                continue

            rules[category] = {
                'description': rule_desc,
                'max_digits': max_digits,
                'examples': extract_examples(rule_desc)
            }

    return rules


def extract_examples(rule_desc):
    """ルール説明文から例を抽出"""
    examples = []
    lines = rule_desc.split('\n')
    for line in lines:
        if '例）' in line or '例:' in line:
            # 例の後のID部分を抽出
            match = re.search(r'([a-z_]+_[a-z]{3}_\d+|[A-Z]+_\d+_\d+|[a-z_]+)', line)
            if match:
                examples.append(match.group(1))
    return examples


def parse_id_pattern(category, rule_desc):
    """ルール説明文からIDパターンを解析

    Returns:
        dict: {
            'prefix': 接頭語,
            'has_series': 作品IDを含むか,
            'has_number': 連番を含むか,
            'number_format': 連番のフォーマット（桁数）,
            'middle_parts': 中間部分（難易度、カテゴリー等）
        }
    """
    pattern = {
        'prefix': '',
        'has_series': False,
        'has_number': False,
        'number_format': '05d',  # デフォルト5桁
        'middle_parts': []
    }

    # 接頭語を抽出
    if '接頭語(' in rule_desc:
        match = re.search(r'接頭語\(([^)]+)\)', rule_desc)
        if match:
            pattern['prefix'] = match.group(1)

    # 作品IDの有無
    if '作品ID' in rule_desc or '作品のローマ字' in rule_desc:
        pattern['has_series'] = True

    # 連番の有無と桁数
    if '5桁' in rule_desc or '連番' in rule_desc:
        pattern['has_number'] = True
        if '3桁' in rule_desc:
            pattern['number_format'] = '03d'
        elif '5桁' in rule_desc:
            pattern['number_format'] = '05d'

    # 中間部分（クエストの難易度やカテゴリー等）
    if 'クエストの難易度' in rule_desc:
        pattern['middle_parts'].append('difficulty')
    if 'クエストカテゴリー' in rule_desc:
        pattern['middle_parts'].append('quest_category')
    if 'エンブレムカテゴリー' in rule_desc:
        pattern['middle_parts'].append('emblem_category')
    if 'コンテンツカテゴリー' in rule_desc:
        pattern['middle_parts'].append('content_category')

    return pattern


def generate_id(category, series_id=None, number=None, **kwargs):
    """IDを生成

    Args:
        category: カテゴリー名
        series_id: 作品コード（例: spy, dan）
        number: 連番
        **kwargs: その他のパラメータ（difficulty, quest_category等）

    Returns:
        str: 生成されたID
    """
    rules = load_numbering_rules()

    if category not in rules:
        print(f"エラー: カテゴリー '{category}' が見つかりません", file=sys.stderr)
        print(f"利用可能なカテゴリー: {', '.join(rules.keys())}", file=sys.stderr)
        sys.exit(1)

    rule = rules[category]
    pattern = parse_id_pattern(category, rule['description'])

    # IDを構築
    id_parts = []

    # 接頭語
    if pattern['prefix']:
        id_parts.append(pattern['prefix'])

    # 中間部分（難易度、カテゴリー等）
    for part_name in pattern['middle_parts']:
        if part_name in kwargs and kwargs[part_name]:
            id_parts.append(kwargs[part_name])

    # 作品ID
    if pattern['has_series']:
        if not series_id:
            print(f"エラー: カテゴリー '{category}' には作品ID（series_id）が必要です", file=sys.stderr)
            sys.exit(1)
        id_parts.append(series_id)

    # 連番
    if pattern['has_number']:
        if number is None:
            print(f"エラー: カテゴリー '{category}' には連番（number）が必要です", file=sys.stderr)
            sys.exit(1)
        formatted_number = format(int(number), pattern['number_format'])
        id_parts.append(formatted_number)

    # 特殊ケース: BGM
    if category == 'BGM':
        # SBG_{管理番号}_{サウンド数}
        if 'mgmt_number' not in kwargs or 'sound_number' not in kwargs:
            print("エラー: BGMには管理番号（mgmt_number）とサウンド数（sound_number）が必要です", file=sys.stderr)
            sys.exit(1)
        return f"SBG_{kwargs['mgmt_number']}_{kwargs['sound_number']}"

    # 特殊ケース: キャラアイコン
    if category == 'キャラアイコン':
        # picon_chara_{作品ID}_{連番} または eicon_chara_{作品ID}_{連番}
        icon_type = kwargs.get('icon_type', 'picon')  # デフォルトはpicon
        chara_type = kwargs.get('chara_type', 'chara')  # デフォルトはchara
        return f"{icon_type}_{chara_type}_{series_id}_{format(int(number), '05d')}"

    return '_'.join(id_parts)


def print_usage():
    """使用方法を表示"""
    print("使用方法: generate_id.py <カテゴリー> <作品ID> <連番> [オプション]", file=sys.stderr)
    print("", file=sys.stderr)
    print("例:", file=sys.stderr)
    print("  generate_id.py キャラ spy 1", file=sys.stderr)
    print("  generate_id.py クエスト dan 1 --difficulty=normal --quest_category=main", file=sys.stderr)
    print("  generate_id.py アイテム glo 1", file=sys.stderr)
    print("  generate_id.py BGM - - --mgmt_number=011 --sound_number=001", file=sys.stderr)
    print("", file=sys.stderr)
    print("利用可能なカテゴリー一覧を表示: list_categories.sh", file=sys.stderr)


def main():
    if len(sys.argv) < 2 or sys.argv[1] in ['-h', '--help']:
        print_usage()
        sys.exit(0 if len(sys.argv) == 2 else 1)

    category = sys.argv[1]
    series_id = sys.argv[2] if len(sys.argv) > 2 and sys.argv[2] != '-' else None
    number = sys.argv[3] if len(sys.argv) > 3 and sys.argv[3] != '-' else None

    # オプションパラメータを解析
    kwargs = {}
    for arg in sys.argv[4:]:
        if arg.startswith('--'):
            key, value = arg[2:].split('=', 1)
            kwargs[key] = value

    generated_id = generate_id(category, series_id, number, **kwargs)
    print(generated_id)


if __name__ == '__main__':
    main()
