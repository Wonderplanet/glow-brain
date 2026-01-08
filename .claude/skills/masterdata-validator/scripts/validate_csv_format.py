#!/usr/bin/env python3
"""
CSV形式検証スクリプト

CSVファイルの形式的な正しさを検証します：
- 改行エスケープ（実際の改行ではなく\\nを使用）
- ダブルクォートの適切な使用
- CSVパーサーでの読み取り可能性

使用方法:
    python validate_csv_format.py <CSVファイルパス>
"""

import sys
import csv
import json
from pathlib import Path
from typing import Dict, List, Any


def validate_csv_format(csv_path: str) -> Dict[str, Any]:
    """
    CSV形式を検証

    Args:
        csv_path: 検証するCSVファイルのパス

    Returns:
        result: 検証結果（JSON形式）
    """
    if not Path(csv_path).exists():
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"CSVファイルが見つかりません: {csv_path}"
        }

    issues = []

    try:
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            content = f.read()

        # 検証1: ダブルクォート内に実際の改行が含まれていないか
        # （簡易チェック: クォート内改行は許容されるが、エスケープ推奨）
        lines = content.split('\n')

        # 検証2: CSVとして正しく解析できるか
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(f)
            row_count = 0
            for row_num, row in enumerate(reader, 1):
                row_count = row_num

                # 各セルをチェック
                for col_num, cell in enumerate(row, 1):
                    # 実際の改行文字が含まれているか確認（警告レベル）
                    if '\n' in cell or '\r' in cell:
                        issues.append({
                            "type": "actual_newline_in_cell",
                            "severity": "warning",
                            "row": row_num,
                            "column": col_num,
                            "message": "セル内に実際の改行文字が含まれています。\\nエスケープシーケンスの使用を推奨します。",
                            "preview": cell[:50] + "..." if len(cell) > 50 else cell
                        })

        # 検証3: ヘッダー行（最初の3行）の形式確認
        with open(csv_path, 'r', encoding='utf-8-sig') as f:
            reader = csv.reader(f)
            rows = [next(reader, None) for _ in range(3)]

        if not rows[0] or rows[0][0].strip() != 'memo':
            issues.append({
                "type": "invalid_header",
                "row": 1,
                "expected": "memo",
                "actual": rows[0][0] if rows[0] else "",
                "message": "1行目は 'memo' で始まる必要があります"
            })

        if not rows[1] or not rows[1][0].startswith('TABLE'):
            issues.append({
                "type": "invalid_header",
                "row": 2,
                "expected": "TABLE,<テーブル名>,...",
                "actual": rows[1][0] if rows[1] else "",
                "message": "2行目は 'TABLE' で始まる必要があります"
            })

        if not rows[2] or not rows[2][0].startswith('ENABLE'):
            issues.append({
                "type": "invalid_header",
                "row": 3,
                "expected": "ENABLE,<カラム名>,...",
                "actual": rows[2][0] if rows[2] else "",
                "message": "3行目は 'ENABLE' で始まる必要があります"
            })

        result = {
            "file": Path(csv_path).name,
            "valid": len([i for i in issues if i.get("severity") != "warning"]) == 0,
            "row_count": row_count,
            "issues": issues
        }

        return result

    except csv.Error as e:
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"CSV解析エラー: {str(e)}"
        }
    except Exception as e:
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"予期しないエラー: {str(e)}"
        }


def main():
    if len(sys.argv) < 2:
        print("使用方法: python validate_csv_format.py <CSVファイルパス>", file=sys.stderr)
        sys.exit(1)

    csv_path = sys.argv[1]
    result = validate_csv_format(csv_path)

    # JSON出力
    print(json.dumps(result, ensure_ascii=False, indent=2))

    # 終了コード
    sys.exit(0 if result.get("valid", False) else 1)


if __name__ == "__main__":
    main()
