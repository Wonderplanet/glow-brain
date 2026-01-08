#!/usr/bin/env python3
"""
DBスキーマ検証スクリプト

CSVデータがDBスキーマと整合性があるか検証します：
- カラムの型チェック
- enum値の検証
- NULL許可/不可のチェック

使用方法:
    python validate_schema.py \
        --csv <CSVファイルパス> \
        --schema <スキーマJSONパス> \
        --table <テーブル名>
"""

import sys
import argparse
import csv
import json
from pathlib import Path
from typing import Dict, List, Any, Optional
from datetime import datetime


def snake_to_pascal(snake_str: str) -> str:
    """snake_caseをPascalCaseに変換"""
    components = snake_str.split('_')
    # 複数形を単数形に変換（簡易版）
    if components[-1].endswith('s'):
        components[-1] = components[-1][:-1]
    return ''.join(x.title() for x in components)


def pascal_to_snake(pascal_str: str) -> str:
    """PascalCaseをsnake_caseに変換して複数形にする"""
    import re
    snake = re.sub(r'(?<!^)(?=[A-Z])', '_', pascal_str).lower()
    # 単数形を複数形に変換（簡易版）
    if not snake.endswith('s'):
        snake += 's'
    return snake


def get_table_schema(schema_data: Dict, table_name: str) -> Optional[Dict]:
    """スキーマデータからテーブル定義を取得"""
    return schema_data.get('databases', {}).get('mst', {}).get('tables', {}).get(table_name)


def read_csv_data(csv_path: str) -> tuple:
    """CSVファイルを読み取ってヘッダーとデータ行を返す"""
    with open(csv_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f)
        rows = list(reader)

    if len(rows) < 4:
        raise ValueError("CSVファイルには最低4行（ヘッダー3行+データ1行以上）必要です")

    # ヘッダー行からカラム名を抽出
    header_row = rows[2]  # 3行目がカラム名行（ENABLE,col1,col2,...）
    if header_row[0].startswith('ENABLE'):
        columns = header_row[1:] if header_row[0] == 'ENABLE' else header_row[1:]
    else:
        columns = header_row

    # データ行（4行目以降、ただし 'd' 行のみ）
    data_rows = []
    for row_num, row in enumerate(rows[3:], start=4):
        if row and row[0] == 'd':
            data_rows.append((row_num, row[1:]))  # 行番号と値（d除外）

    return columns, data_rows


def validate_value_type(value: str, column_def: Dict) -> Optional[str]:
    """値の型を検証"""
    col_type = column_def.get('type', '')
    nullable = column_def.get('nullable', False)

    # NULL値チェック
    if value == '' or value is None:
        if not nullable:
            return f"NULL不可カラムに空値が設定されています"
        return None

    # 型チェック
    if 'int' in col_type.lower():
        try:
            int(value)
        except ValueError:
            return f"整数型が期待されますが、'{value}' は整数ではありません"

    elif 'decimal' in col_type.lower() or 'float' in col_type.lower() or 'double' in col_type.lower():
        try:
            float(value)
        except ValueError:
            return f"数値型が期待されますが、'{value}' は数値ではありません"

    elif 'date' in col_type.lower() or 'timestamp' in col_type.lower():
        # ISO 8601形式チェック（簡易版）
        if value and not (len(value) == 19 and value[4] == '-' and value[7] == '-'):
            return f"日時形式（YYYY-MM-DD HH:MM:SS）が期待されますが、'{value}' は形式が異なります"

    # enum値チェック
    enum_values = column_def.get('enum_values')
    if enum_values and value not in enum_values:
        return f"enum値 {enum_values} のいずれかが期待されますが、'{value}' は許可されていません"

    return None


def validate_schema(csv_path: str, schema_path: str, table_name: Optional[str] = None) -> Dict[str, Any]:
    """
    CSVデータをDBスキーマと照合

    Args:
        csv_path: CSVファイルのパス
        schema_path: スキーマJSONのパス
        table_name: テーブル名（Noneの場合はCSVファイル名から推測）

    Returns:
        result: 検証結果（JSON形式）
    """
    # ファイル存在確認
    if not Path(csv_path).exists():
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"CSVファイルが見つかりません: {csv_path}"
        }

    if not Path(schema_path).exists():
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"スキーマファイルが見つかりません: {schema_path}"
        }

    # テーブル名の推測
    if not table_name:
        csv_filename = Path(csv_path).stem  # 拡張子なしのファイル名
        table_name = pascal_to_snake(csv_filename)

    # スキーマ読み取り
    with open(schema_path, 'r', encoding='utf-8') as f:
        schema_data = json.load(f)

    table_schema = get_table_schema(schema_data, table_name)
    if not table_schema:
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"テーブル '{table_name}' がスキーマに見つかりません"
        }

    # CSV読み取り
    try:
        columns, data_rows = read_csv_data(csv_path)
    except Exception as e:
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"CSV読み取りエラー: {str(e)}"
        }

    # 検証実施
    issues = []
    schema_columns = table_schema.get('columns', {})

    # カラム数チェック
    if len(columns) != len(schema_columns):
        issues.append({
            "type": "column_count_mismatch",
            "expected": len(schema_columns),
            "actual": len(columns),
            "message": f"カラム数が一致しません（期待: {len(schema_columns)}, 実際: {len(columns)}）"
        })

    # 各データ行を検証
    for row_num, row_data in data_rows:
        for col_idx, (col_name, value) in enumerate(zip(columns, row_data)):
            column_def = schema_columns.get(col_name)

            if not column_def:
                issues.append({
                    "type": "unknown_column",
                    "row": row_num,
                    "column": col_name,
                    "message": f"カラム '{col_name}' はスキーマに定義されていません"
                })
                continue

            # 型・enum値検証
            error_msg = validate_value_type(value, column_def)
            if error_msg:
                issues.append({
                    "type": "value_validation_error",
                    "row": row_num,
                    "column": col_name,
                    "value": value,
                    "message": error_msg,
                    "column_type": column_def.get('type'),
                    "nullable": column_def.get('nullable', False)
                })

    # 結果作成
    result = {
        "file": Path(csv_path).name,
        "table": table_name,
        "valid": len(issues) == 0,
        "data_rows": len(data_rows),
        "issues": issues
    }

    return result


def main():
    parser = argparse.ArgumentParser(
        description='CSVデータのDBスキーマ検証'
    )
    parser.add_argument(
        '--csv',
        required=True,
        help='CSVファイルのパス'
    )
    parser.add_argument(
        '--schema',
        required=True,
        help='スキーマJSONファイルのパス'
    )
    parser.add_argument(
        '--table',
        help='テーブル名（省略時はCSVファイル名から推測）'
    )

    args = parser.parse_args()

    # 検証実行
    result = validate_schema(args.csv, args.schema, args.table)

    # JSON出力
    print(json.dumps(result, ensure_ascii=False, indent=2))

    # 終了コード
    sys.exit(0 if result.get("valid", False) else 1)


if __name__ == "__main__":
    main()
