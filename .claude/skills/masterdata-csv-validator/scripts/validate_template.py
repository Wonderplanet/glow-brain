#!/usr/bin/env python3
"""
マスタデータCSVテンプレート照合スクリプト

生成されたマスタデータCSVとテンプレートCSVを比較し、
不一致箇所をJSON形式で出力します。

使用方法:
    python validate_masterdata_template.py \
        --generated <生成CSVパス> \
        --template <テンプレートCSVパス>
"""

import sys
import argparse
import csv
import json
from pathlib import Path
from typing import Dict, List, Any


def read_csv_header(csv_path: str) -> tuple:
    """
    CSVファイルの先頭3行（ヘッダー）を読み取る

    Returns:
        (row1, row2, row3): ヘッダー3行のタプル
    """
    with open(csv_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f)
        rows = [next(reader, None) for _ in range(3)]

    return tuple(rows)


def validate_header_format(row1: List[str], row2: List[str], row3: List[str]) -> List[Dict[str, Any]]:
    """
    ヘッダー3行の形式をチェック

    Args:
        row1: 1行目（memo行）
        row2: 2行目（TABLE行）
        row3: 3行目（ENABLE/カラム名行）

    Returns:
        issues: 不一致のリスト
    """
    issues = []

    # Row 1: "memo" のみであることを確認
    if not row1 or row1[0].strip() != 'memo':
        issues.append({
            "type": "header_format",
            "row": 1,
            "expected": "memo",
            "actual": row1[0] if row1 else ""
        })

    # Row 2: "TABLE" で始まることを確認
    if not row2 or not row2[0].startswith('TABLE'):
        issues.append({
            "type": "header_format",
            "row": 2,
            "expected": "TABLE,<テーブル名>,...",
            "actual": row2[0] if row2 else ""
        })

    # Row 3: "ENABLE" で始まることを確認
    if not row3 or not row3[0].startswith('ENABLE'):
        issues.append({
            "type": "header_format",
            "row": 3,
            "expected": "ENABLE,<カラム名>,...",
            "actual": row3[0] if row3 else ""
        })

    return issues


def extract_columns(row3: List[str]) -> List[str]:
    """
    Row 3（カラム名行）からカラム名のリストを抽出

    Args:
        row3: 3行目（ENABLE,カラム名1,カラム名2,...）

    Returns:
        columns: カラム名のリスト（"ENABLE"は除外）
    """
    if not row3:
        return []

    # 最初の要素が "ENABLE" の場合は除外
    if row3[0].startswith('ENABLE'):
        # カンマで分割されている場合を考慮
        if row3[0] == 'ENABLE':
            return row3[1:]
        else:
            # "ENABLE" の後にカンマがない場合
            return row3[1:] if len(row3) > 1 else []

    return row3


def validate_columns(generated_columns: List[str], template_columns: List[str]) -> List[Dict[str, Any]]:
    """
    カラム順序と存在をチェック

    Args:
        generated_columns: 生成CSVのカラムリスト
        template_columns: テンプレートCSVのカラムリスト

    Returns:
        issues: 不一致のリスト
    """
    issues = []

    # カラム順序の一致チェック
    if generated_columns != template_columns:
        issues.append({
            "type": "column_order",
            "expected": template_columns,
            "actual": generated_columns
        })

    # 欠損カラムのチェック
    generated_set = set(generated_columns)
    template_set = set(template_columns)

    missing_columns = template_set - generated_set
    for col in missing_columns:
        issues.append({
            "type": "missing_column",
            "column": col
        })

    # 余分なカラムのチェック
    extra_columns = generated_set - template_set
    for col in extra_columns:
        issues.append({
            "type": "extra_column",
            "column": col
        })

    return issues


def validate_masterdata_csv(generated_path: str, template_path: str) -> Dict[str, Any]:
    """
    マスタデータCSVをテンプレートと照合

    Args:
        generated_path: 生成されたCSVのパス
        template_path: テンプレートCSVのパス

    Returns:
        result: 検証結果（JSON形式）
    """
    # ファイル存在確認
    if not Path(generated_path).exists():
        return {
            "file": Path(generated_path).name,
            "valid": False,
            "error": f"生成CSVが見つかりません: {generated_path}"
        }

    if not Path(template_path).exists():
        return {
            "file": Path(generated_path).name,
            "valid": False,
            "error": f"テンプレートCSVが見つかりません: {template_path}"
        }

    # ヘッダー読み取り
    try:
        gen_row1, gen_row2, gen_row3 = read_csv_header(generated_path)
        tpl_row1, tpl_row2, tpl_row3 = read_csv_header(template_path)
    except Exception as e:
        return {
            "file": Path(generated_path).name,
            "valid": False,
            "error": f"CSV読み取りエラー: {str(e)}"
        }

    # 検証実施
    issues = []

    # ヘッダー形式チェック
    header_issues = validate_header_format(gen_row1, gen_row2, gen_row3)
    issues.extend(header_issues)

    # カラムチェック
    gen_columns = extract_columns(gen_row3)
    tpl_columns = extract_columns(tpl_row3)
    column_issues = validate_columns(gen_columns, tpl_columns)
    issues.extend(column_issues)

    # 結果作成
    result = {
        "file": Path(generated_path).name,
        "valid": len(issues) == 0,
        "issues": issues
    }

    return result


def main():
    parser = argparse.ArgumentParser(
        description='マスタデータCSVとsheet_schemaテンプレートCSVの照合'
    )
    # --csv は --generated の新しい名前（後方互換のため --generated も残す）
    parser.add_argument(
        '--csv',
        dest='csv',
        help='検証対象CSVファイルのパス'
    )
    parser.add_argument(
        '--generated',
        dest='generated',
        help='（非推奨）--csv を使用してください'
    )
    # --reference-csv は --template の新しい名前（後方互換のため --template も残す）
    parser.add_argument(
        '--reference-csv',
        dest='reference_csv',
        help='テンプレートCSVファイルのパス'
    )
    parser.add_argument(
        '--template',
        dest='template',
        help='（非推奨）--reference-csv を使用してください'
    )
    parser.add_argument(
        '--mode',
        choices=['sheet_schema'],
        default='sheet_schema',
        help='検証モード（sheet_schema のみ対応）'
    )

    args = parser.parse_args()

    # 新旧引数の統合（新しい名前を優先）
    csv_path = args.csv or args.generated
    template_path = args.reference_csv or args.template

    if not csv_path:
        parser.error('--csv（または非推奨の --generated）は必須です')
    if not template_path:
        parser.error('--reference-csv（または非推奨の --template）は必須です')

    if args.generated:
        print(
            '警告: --generated は非推奨です。--csv を使用してください',
            file=sys.stderr
        )
    if args.template:
        print(
            '警告: --template は非推奨です。--reference-csv を使用してください',
            file=sys.stderr
        )

    # 検証実行
    result = validate_masterdata_csv(csv_path, template_path)

    # JSON出力
    print(json.dumps(result, ensure_ascii=False, indent=2))

    # 終了コード
    sys.exit(0 if result.get("valid", False) else 1)


if __name__ == "__main__":
    main()
