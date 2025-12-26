#!/usr/bin/env python3
"""
実際のマスタデータCSVから設定例を抽出するスクリプト

Usage:
    python3 sample_masterdata.py <csv_file_path> [--limit N]
    python3 sample_masterdata.py projects/glow-masterdata/MstMissionDaily.csv --limit 5
"""

import argparse
import csv
import json
import sys
from pathlib import Path
from typing import List, Dict, Any


def read_csv_file(csv_file: Path) -> tuple[List[str], List[Dict[str, str]]]:
    """
    GLOWプロジェクトのマスタデータCSVを読み取る

    実際のマスタデータファイル（projects/glow-masterdata/*.csv）は標準的なCSV形式：
    - 1行目: 列名（ヘッダー）
    - 2行目以降: データ

    注意: sheet_schema/*.csvファイルとは形式が異なる（そちらは4行形式）

    Returns:
        (headers, rows)
    """
    try:
        with open(csv_file, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            headers = reader.fieldnames

            if not headers:
                print(f"No headers found in CSV: {csv_file}", file=sys.stderr)
                return [], []

            rows = list(reader)

        return list(headers), rows

    except Exception as e:
        print(f"Error reading CSV: {e}", file=sys.stderr)
        return [], []


def sample_diverse_rows(rows: List[Dict[str, str]], limit: int = 5) -> List[Dict[str, str]]:
    """
    多様性のある代表的なサンプルを選択

    選択基準:
    - 最初の数行（典型的なパターン）
    - 異なるパターンを持つ行
    """
    if len(rows) <= limit:
        return rows

    # 最初の行は必ず含める
    samples = [rows[0]]

    # 残りの行から多様性のあるサンプルを選択
    step = max(1, len(rows) // (limit - 1))
    for i in range(step, len(rows), step):
        if len(samples) >= limit:
            break
        samples.append(rows[i])

    return samples


def main():
    parser = argparse.ArgumentParser(description="マスタデータCSVから設定例を抽出")
    parser.add_argument("csv_file", help="CSVファイルパス")
    parser.add_argument("--limit", "-l", type=int, default=5, help="抽出する行数（デフォルト: 5）")
    parser.add_argument("--output", "-o", help="出力JSONファイルパス（省略時は標準出力）")

    args = parser.parse_args()

    csv_file = Path(args.csv_file)
    if not csv_file.exists():
        print(f"Error: File not found: {csv_file}", file=sys.stderr)
        sys.exit(1)

    headers, rows = read_csv_file(csv_file)

    if not headers or not rows:
        print("No data found in CSV", file=sys.stderr)
        sys.exit(1)

    samples = sample_diverse_rows(rows, args.limit)

    result = {
        "file": str(csv_file.name),
        "headers": headers,
        "sample_count": len(samples),
        "samples": samples
    }

    output_json = json.dumps(result, ensure_ascii=False, indent=2)

    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            f.write(output_json)
        print(f"Samples saved to {args.output}", file=sys.stderr)
    else:
        print(output_json)


if __name__ == "__main__":
    main()
