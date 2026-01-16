#!/usr/bin/env python3
"""
CSVファイル名に基づいてmaster_tables_schema.jsonをフィルタリングするスクリプト

Usage:
    python scripts/filter_master_tables_schema.py [--dry-run]
"""

import json
import re
import sys
from pathlib import Path
from typing import Dict, List, Set, Tuple


def camel_to_snake(name: str) -> str:
    """
    アッパーキャメルケースをスネークケースに変換する
    例: MstAdventBattle -> mst_advent_battle
    """
    # 連続する大文字を処理（例: I18n -> i18n）
    s1 = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', name)
    # 小文字の後の大文字を処理
    return re.sub('([a-z0-9])([A-Z])', r'\1_\2', s1).lower()


def get_csv_files(masterdata_dir: Path) -> List[str]:
    """
    glow-masterdataディレクトリ直下のCSVファイル名（拡張子なし）を取得する
    """
    csv_files = []
    for csv_path in masterdata_dir.glob('*.csv'):
        # 拡張子を除去
        csv_name = csv_path.stem
        csv_files.append(csv_name)
    return sorted(csv_files)


def pluralize(word: str) -> List[str]:
    """
    単数形の単語を複数形に変換する候補を生成する

    Args:
        word: 単数形の単語

    Returns:
        複数形の候補リスト
    """
    candidates = []

    # 1. そのまま（すでに複数形の場合）
    candidates.append(word)

    # 2. 末尾に 's' を追加
    candidates.append(f"{word}s")

    # 3. 末尾が 'y' で終わる場合、'ies' に変換
    if word.endswith('y'):
        candidates.append(f"{word[:-1]}ies")

    # 4. 末尾が 'x', 's', 'ch', 'sh' で終わる場合、'es' を追加
    if word.endswith(('x', 's', 'ch', 'sh')):
        candidates.append(f"{word}es")

    # 5. 末尾が 'fe' で終わる場合、'ves' に変換
    if word.endswith('fe'):
        candidates.append(f"{word[:-2]}ves")

    # 6. 末尾が 'f' で終わる場合、'ves' に変換
    if word.endswith('f') and not word.endswith('ff'):
        candidates.append(f"{word[:-1]}ves")

    return candidates


def csv_name_to_table_candidates(csv_name: str) -> List[str]:
    """
    CSVファイル名から可能性のあるテーブル名候補を生成する

    Args:
        csv_name: CSVファイル名（拡張子なし）例: MstAdventBattle, MstAdventBattleI18n

    Returns:
        候補となるテーブル名のリスト（スネークケース・複数形）
    """
    # I18nサフィックスを持つかチェック
    has_i18n_suffix = csv_name.endswith('I18n')

    if has_i18n_suffix:
        # I18nサフィックスを除去
        base_name = csv_name[:-4]  # 'I18n' を除去
        # ベース名をスネークケースに変換
        snake_base = camel_to_snake(base_name)
        # ベース名を複数形に変換して、_i18n を追加
        base_plurals = pluralize(snake_base)
        candidates = [f"{plural}_i18n" for plural in base_plurals]
    else:
        # 通常のテーブル名処理
        snake_name = camel_to_snake(csv_name)
        candidates = pluralize(snake_name)

    return candidates


def match_csv_to_table(csv_name: str, table_names: Set[str]) -> Tuple[str, str]:
    """
    CSVファイル名をJSONテーブル名にマッチングする

    Args:
        csv_name: CSVファイル名（拡張子なし）
        table_names: JSONに含まれるテーブル名のセット

    Returns:
        (csv_name, matched_table_name) または (csv_name, None)
    """
    candidates = csv_name_to_table_candidates(csv_name)

    for candidate in candidates:
        if candidate in table_names:
            return (csv_name, candidate)

    return (csv_name, None)


def filter_schema(
    input_json_path: Path,
    masterdata_dir: Path,
    output_json_path: Path,
    dry_run: bool = False
) -> None:
    """
    CSVファイル名に基づいてスキーマJSONをフィルタリングする
    """
    print("=" * 80)
    print("CSVファイルに基づくJSONスキーマフィルタリング")
    print("=" * 80)
    print()

    # JSONファイルを読み込む
    print(f"📖 JSONファイルを読み込み中: {input_json_path}")
    with open(input_json_path, 'r', encoding='utf-8') as f:
        schema_data = json.load(f)

    # テーブル名一覧を取得
    tables = schema_data.get('databases', {}).get('mst', {}).get('tables', {})
    table_names = set(tables.keys())
    print(f"   JSONに含まれるテーブル数: {len(table_names)}")
    print()

    # CSVファイル名一覧を取得
    print(f"📂 CSVファイルを検索中: {masterdata_dir}")
    csv_files = get_csv_files(masterdata_dir)
    print(f"   見つかったCSVファイル数: {len(csv_files)}")
    print()

    # マッチング処理
    print("🔍 マッチング処理開始")
    print("-" * 80)

    matched_tables = {}
    unmatched_csvs = []

    for csv_name in csv_files:
        csv_name_display, matched_table = match_csv_to_table(csv_name, table_names)

        if matched_table:
            matched_tables[matched_table] = csv_name_display
            print(f"✅ {csv_name_display:50s} → {matched_table}")
        else:
            unmatched_csvs.append(csv_name_display)
            print(f"❌ {csv_name_display:50s} → (マッチなし)")

    print("-" * 80)
    print()

    # 結果サマリー
    print("📊 マッチング結果")
    print(f"   マッチしたCSVファイル: {len(matched_tables)}/{len(csv_files)}")
    print(f"   マッチしなかったCSVファイル: {len(unmatched_csvs)}/{len(csv_files)}")
    print()

    if unmatched_csvs:
        print("⚠️  マッチしなかったCSVファイル:")
        for csv_name in unmatched_csvs:
            print(f"   - {csv_name}")
        print()

    # ドライランモードの場合はここで終了
    if dry_run:
        print("🏃 ドライランモードのため、ファイルは作成されません")
        print()
        return

    # フィルタリング済みのスキーマを作成
    filtered_tables = {
        table_name: tables[table_name]
        for table_name in matched_tables.keys()
    }

    filtered_schema = {
        'databases': {
            'mst': {
                'tables': filtered_tables
            }
        }
    }

    # 出力ファイルに書き込む
    print(f"💾 フィルタリング済みJSONを出力中: {output_json_path}")
    with open(output_json_path, 'w', encoding='utf-8') as f:
        json.dump(filtered_schema, f, ensure_ascii=False, indent=4)

    print(f"   出力されたテーブル数: {len(filtered_tables)}")
    print()
    print("✨ 完了しました！")
    print()


def main():
    """メイン処理"""
    # 引数チェック
    dry_run = '--dry-run' in sys.argv

    # パスの設定
    script_dir = Path(__file__).parent
    project_root = script_dir.parent

    input_json_path = project_root / 'projects/glow-server/api/database/schema/exports/master_tables_schema.json'
    masterdata_dir = project_root / 'projects/glow-masterdata'
    output_json_path = project_root / 'projects/glow-server/api/database/schema/exports/master_tables_schema_filtered.json'

    # ファイル存在チェック
    if not input_json_path.exists():
        print(f"❌ エラー: 入力JSONファイルが見つかりません: {input_json_path}", file=sys.stderr)
        sys.exit(1)

    if not masterdata_dir.exists():
        print(f"❌ エラー: masterdataディレクトリが見つかりません: {masterdata_dir}", file=sys.stderr)
        sys.exit(1)

    # フィルタリング処理実行
    filter_schema(input_json_path, masterdata_dir, output_json_path, dry_run)


if __name__ == '__main__':
    main()
