#!/usr/bin/env python3
"""
GLOWマスタデータのID検証スクリプト

ID割り振りルール.csvから採番ルールを読み取り、指定されたIDが準拠しているかをチェックします。
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
                'max_digits': max_digits
            }

    return rules


def build_regex_patterns():
    """全カテゴリーの正規表現パターンを動的に構築"""
    rules = load_numbering_rules()
    patterns = {}

    for category, rule in rules.items():
        desc = rule['description']
        regex_pattern = None

        # カテゴリーごとのパターンを動的に構築
        if 'キャラ' in category:
            # chara_{作品ID}_{5桁連番} または enemy_{作品ID}_{5桁連番}
            regex_pattern = r'^(chara|enemy)_[a-z]{3}_\d{5}$'

        elif 'クエスト' in category:
            # quest_{難易度}_{カテゴリー}_{作品ID}_{5桁連番}
            # 難易度はnullの可能性もある
            regex_pattern = r'^quest_([a-z]+_)*[a-z]+_[a-z]{3}_\d{5}$'

        elif 'アイテム' in category:
            # {接頭語}_{作品ID}_{5桁連番}
            regex_pattern = r'^[a-z]+_[a-z]{3}_\d{5}$'

        elif 'エンブレム' in category:
            # emblem_{カテゴリー}_{作品ID}_{5桁連番}
            regex_pattern = r'^emblem_[a-z]+_[a-z]{3}_\d{5}$'

        elif '背景' in category:
            # background_{作品ID}_{5桁連番}
            regex_pattern = r'^background_[a-z]{3}_\d{5}$'

        elif 'コンテンツ' in category:
            # contents_{カテゴリー}_{作品ID}_{5桁連番}
            regex_pattern = r'^contents_[a-z]+_[a-z]{3}_\d{5}$'

        elif '図鑑' in category:
            # book_{作品ID}_{5桁連番}
            regex_pattern = r'^book_[a-z]{3}_\d{5}$'

        elif 'BGM' in category:
            # SBG_{管理番号}_{サウンド数}
            regex_pattern = r'^SBG_\d{3}_\d{3}$'

        elif 'アバターアイコン' in category:
            # unit_icon_chara_{作品ID}_{5桁連番} または unit_icon_enemy_{作品ID}_{5桁連番}
            regex_pattern = r'^unit_icon_(chara|enemy)_[a-z]{3}_\d{5}$'

        elif 'キャラアイコン' in category:
            # picon_chara_{作品ID}_{5桁連番} または eicon_chara_{作品ID}_{5桁連番}
            # または eicon_enemy_{作品ID}_{5桁連番}
            regex_pattern = r'^(picon|eicon)_(chara|enemy)_[a-z]{3}_\d{5}$'

        elif 'ゲート' in category:
            # outpost_{作品ID}_{5桁連番} または enemy_{作品ID}_{5桁連番}
            regex_pattern = r'^(outpost|enemy)_[a-z]{3}_\d{5}$'

        elif '作品' in category:
            # 作品コード（3文字の小文字）
            regex_pattern = r'^[a-z]{3}$'

        if regex_pattern:
            patterns[category] = {
                'regex': regex_pattern,
                'description': desc,
                'max_digits': rule['max_digits']
            }

    return patterns


def validate_id(id_str, category=None):
    """IDを検証

    Args:
        id_str: 検証するID文字列
        category: カテゴリー（指定した場合、そのカテゴリーのみでチェック）

    Returns:
        dict: {
            'valid': True/False,
            'matched_category': マッチしたカテゴリー,
            'pattern': マッチしたパターン,
            'issues': 問題点のリスト
        }
    """
    patterns = build_regex_patterns()

    result = {
        'valid': False,
        'matched_category': None,
        'pattern': None,
        'issues': []
    }

    # カテゴリー指定がある場合
    if category:
        if category not in patterns:
            result['issues'].append(f"カテゴリー '{category}' が見つかりません")
            return result

        pattern_info = patterns[category]
        if re.match(pattern_info['regex'], id_str):
            result['valid'] = True
            result['matched_category'] = category
            result['pattern'] = pattern_info['regex']
        else:
            result['issues'].append(f"カテゴリー '{category}' のパターンに一致しません")
            result['issues'].append(f"期待されるパターン: {pattern_info['description'][:100]}")

        return result

    # カテゴリー指定がない場合、全パターンでマッチング
    for cat, pattern_info in patterns.items():
        if re.match(pattern_info['regex'], id_str):
            result['valid'] = True
            result['matched_category'] = cat
            result['pattern'] = pattern_info['regex']
            return result

    # どのパターンにもマッチしない場合
    result['issues'].append("どの採番ルールにも一致しませんでした")

    # IDの形式から推測される問題点を提示
    if '_' not in id_str:
        result['issues'].append("接頭語や区切り文字（_）が不足している可能性があります")

    parts = id_str.split('_')
    if len(parts) >= 2:
        # 作品コードのチェック
        series_candidates = [p for p in parts if len(p) == 3 and p.islower() and p.isalpha()]
        if not series_candidates:
            result['issues'].append("作品コード（3文字の小文字）が含まれていない可能性があります")

    return result


def check_duplicate(id_str, csv_path=None):
    """既存CSVファイルとの重複をチェック

    Args:
        id_str: チェックするID
        csv_path: チェック対象のCSVファイルパス（省略時はprojects/glow-masterdata/配下を検索）

    Returns:
        list: 重複が見つかったファイルパスのリスト
    """
    duplicates = []

    if csv_path:
        # 指定されたCSVファイルのみチェック
        csv_files = [Path(csv_path)]
    else:
        # projects/glow-masterdata/配下の全CSVをチェック
        script_dir = Path(__file__).parent
        project_root = script_dir.parent.parent.parent.parent
        masterdata_dir = project_root / "projects/glow-masterdata"

        if not masterdata_dir.exists():
            return duplicates

        csv_files = list(masterdata_dir.glob("*.csv"))

    for csv_file in csv_files:
        try:
            with open(csv_file, 'r', encoding='utf-8') as f:
                content = f.read()
                if id_str in content:
                    duplicates.append(str(csv_file))
        except Exception:
            # CSVの読み取りエラーは無視
            pass

    return duplicates


def print_usage():
    """使用方法を表示"""
    print("使用方法: validate_id.py <ID> [--category=<カテゴリー>] [--csv-path=<CSVパス>]", file=sys.stderr)
    print("", file=sys.stderr)
    print("例:", file=sys.stderr)
    print("  validate_id.py chara_spy_00001", file=sys.stderr)
    print("  validate_id.py quest_main_normal_dan_00001 --category=クエスト", file=sys.stderr)
    print("  validate_id.py chara_spy_00001 --csv-path=projects/glow-masterdata/MstHero.csv", file=sys.stderr)


def main():
    if len(sys.argv) < 2 or sys.argv[1] in ['-h', '--help']:
        print_usage()
        sys.exit(0 if len(sys.argv) == 2 else 1)

    id_str = sys.argv[1]
    category = None
    csv_path = None

    # オプションパラメータを解析
    for arg in sys.argv[2:]:
        if arg.startswith('--category='):
            category = arg.split('=', 1)[1]
        elif arg.startswith('--csv-path='):
            csv_path = arg.split('=', 1)[1]

    # ID検証
    print(f"=== ID検証結果: {id_str} ===\n")

    result = validate_id(id_str, category)

    if result['valid']:
        print("✅ 検証結果: 準拠")
        print(f"   マッチしたカテゴリー: {result['matched_category']}")
        print(f"   パターン: {result['pattern']}")
    else:
        print("❌ 検証結果: 非準拠")
        for issue in result['issues']:
            print(f"   - {issue}")

    # 重複チェック
    print("\n--- 重複チェック ---")
    duplicates = check_duplicate(id_str, csv_path)

    if duplicates:
        print(f"⚠️  警告: 以下のファイルで既に使用されています:")
        for dup in duplicates:
            print(f"   - {dup}")
    else:
        print("✅ 重複なし")

    print()


if __name__ == '__main__':
    main()
