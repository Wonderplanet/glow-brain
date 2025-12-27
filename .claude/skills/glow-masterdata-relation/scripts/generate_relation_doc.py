#!/usr/bin/env python3
"""
GLOWマスターデータのリレーションドキュメント生成スクリプト

Usage:
    python3 scripts/generate_relation_doc.py <keywords> [output_file]

Examples:
    python3 scripts/generate_relation_doc.py "quest,stage,in_game"
    python3 scripts/generate_relation_doc.py "gacha" マスタデータ/docs/ガチャ_リレーション.md
"""

import sys
import json
import re
from pathlib import Path
from typing import List, Dict, Set, Tuple
from datetime import datetime

SCHEMA_PATH = "projects/glow-server/api/database/schema/exports/master_tables_schema.json"


def load_schema() -> Dict:
    """DBスキーマJSONを読み込む"""
    schema_file = Path(SCHEMA_PATH)
    if not schema_file.exists():
        raise FileNotFoundError(f"Schema file not found: {SCHEMA_PATH}")

    with open(schema_file, 'r', encoding='utf-8') as f:
        return json.load(f)


def search_tables(schema: Dict, keywords: List[str]) -> List[str]:
    """キーワードに一致するテーブルを検索"""
    all_tables = schema.get('databases', {}).get('mst', {}).get('tables', {}).keys()

    # キーワードを正規表現パターンに変換
    patterns = [re.compile(kw, re.IGNORECASE) for kw in keywords]

    matching_tables = []
    for table in all_tables:
        for pattern in patterns:
            if pattern.search(table):
                matching_tables.append(table)
                break

    return sorted(matching_tables)


def get_table_columns(schema: Dict, table_name: str) -> Dict:
    """テーブルのカラム情報を取得"""
    return schema.get('databases', {}).get('mst', {}).get('tables', {}).get(table_name, {}).get('columns', {})


def detect_foreign_keys(table_name: str, columns: Dict) -> List[Tuple[str, str]]:
    """外部キー相当のカラムを検出"""
    fk_patterns = [
        (r'mst_([a-z_]+)_id', 'mst_{}s'),  # mst_unit_id -> mst_units
        (r'opr_([a-z_]+)_id', 'opr_{}s'),  # opr_gacha_id -> opr_gachas
        (r'prev_mst_([a-z_]+)_id', 'mst_{}s'),  # prev_mst_stage_id -> mst_stages (self-reference)
    ]

    foreign_keys = []
    for col_name in columns.keys():
        for pattern, target_template in fk_patterns:
            match = re.match(pattern, col_name)
            if match:
                base_name = match.group(1)
                # 単数形を複数形に変換（簡易版）
                if base_name.endswith('y'):
                    plural = base_name[:-1] + 'ies'
                elif base_name.endswith('s'):
                    plural = base_name + 'es'
                else:
                    plural = base_name + 's'

                target_table = target_template.format(plural)
                foreign_keys.append((col_name, target_table))

    return foreign_keys


def generate_er_diagram(tables: List[str], schema: Dict) -> str:
    """mermaid ER図を生成"""
    lines = ["```mermaid", "erDiagram"]

    # リレーションを収集
    relations = []
    table_definitions = []

    for table in tables:
        columns = get_table_columns(schema, table)
        fks = detect_foreign_keys(table, columns)

        # テーブル定義
        main_columns = []
        for col_name, col_info in list(columns.items())[:10]:  # 最初の10カラムのみ
            col_type = col_info.get('type', 'string')
            is_pk = col_name == 'id'
            pk_marker = ' PK' if is_pk else ''
            main_columns.append(f"        {col_type} {col_name}{pk_marker}")

        if main_columns:
            table_definitions.append(f"    {table} {{\n" + "\n".join(main_columns) + "\n    }")

        # リレーション
        for fk_col, target_table in fks:
            if target_table in tables:
                # 自己参照の場合
                if target_table == table:
                    relations.append(f'    {table} ||--o| {table} : "{fk_col}"')
                else:
                    relations.append(f'    {table} }}o--|| {target_table} : "{fk_col}"')

    lines.extend(relations)
    lines.append("")
    lines.extend(table_definitions)
    lines.append("```")

    return "\n".join(lines)


def generate_table_list(tables: List[str], schema: Dict) -> str:
    """テーブル一覧を生成"""
    lines = ["## テーブル一覧", ""]

    for table in tables:
        columns = get_table_columns(schema, table)
        # コメントやメタデータがあれば取得（今回は簡易版）
        lines.append(f"- `{table}` ({len(columns)} columns)")

    return "\n".join(lines)


def generate_relation_summary(tables: List[str], schema: Dict) -> str:
    """リレーションパターンまとめを生成"""
    lines = ["## リレーションパターン", "", "| 親テーブル | 子テーブル | 外部キー |", "|----------|----------|---------|"]

    for table in tables:
        columns = get_table_columns(schema, table)
        fks = detect_foreign_keys(table, columns)

        for fk_col, target_table in fks:
            if target_table != table and target_table in tables:
                lines.append(f"| {target_table} | {table} | {fk_col} |")

    return "\n".join(lines)


def generate_document(keywords: List[str], output_file: str = None):
    """ドキュメント全体を生成"""
    print(f"📊 マスターデータリレーションドキュメント生成")
    print(f"   キーワード: {', '.join(keywords)}")

    # スキーマ読み込み
    schema = load_schema()
    print(f"✅ スキーマ読み込み完了")

    # テーブル検索
    tables = search_tables(schema, keywords)
    print(f"✅ {len(tables)}個のテーブルを検出")

    if not tables:
        print("❌ キーワードに一致するテーブルが見つかりませんでした")
        sys.exit(1)

    # ドキュメント生成
    title = f"{'・'.join([kw.title() for kw in keywords])} マスターテーブルリレーション"

    doc_lines = [
        f"# {title}",
        "",
        f"> 調査日: {datetime.now().strftime('%Y-%m-%d')}",
        f"> 対象: glow-server, glow-masterdata",
        "",
        "## 概要",
        "",
        f"{', '.join(keywords)} 関連のマスターテーブル（mst_*, opr_*）のリレーション構造をまとめたドキュメントです。",
        "",
        "## 全体像（ER図）",
        "",
        generate_er_diagram(tables, schema),
        "",
        "---",
        "",
        generate_table_list(tables, schema),
        "",
        "---",
        "",
        generate_relation_summary(tables, schema),
        "",
        "---",
        "",
        "## 参考情報",
        "",
        "### スキーマファイル",
        f"- `{SCHEMA_PATH}`",
        "",
        "### マスターデータCSV",
        "- `projects/glow-masterdata/*.csv`",
        "",
    ]

    document = "\n".join(doc_lines)

    # ファイル出力
    if output_file:
        output_path = Path(output_file)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        output_path.write_text(document, encoding='utf-8')
        print(f"✅ ドキュメント生成完了: {output_file}")
    else:
        print("\n" + "="*80)
        print(document)
        print("="*80)


def main():
    if len(sys.argv) < 2:
        print(__doc__)
        sys.exit(1)

    keywords_arg = sys.argv[1]
    keywords = [kw.strip() for kw in keywords_arg.split(',')]

    output_file = sys.argv[2] if len(sys.argv) > 2 else None

    generate_document(keywords, output_file)


if __name__ == '__main__':
    main()
