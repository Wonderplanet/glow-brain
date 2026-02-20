#!/usr/bin/env python3
"""
GLOWマスタデータのID採番パターン分析スクリプト
各CSVファイルのIDカラムを分析し、通算連番情報が含まれているパターンを洗い出す
"""

import csv
import re
from pathlib import Path
from collections import defaultdict
from typing import List, Dict, Tuple

def read_csv_sample(filepath: Path, max_rows: int = 100) -> List[Dict]:
    """CSVファイルの先頭max_rows行を読み込む"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            reader = csv.DictReader(f)
            rows = []
            for i, row in enumerate(reader):
                if i >= max_rows:
                    break
                rows.append(row)
            return rows
    except Exception as e:
        print(f"Error reading {filepath}: {e}")
        return []

def analyze_id_pattern(ids: List[str], table_name: str) -> List[Dict]:
    """
    IDのパターンを分析
    通算連番がIDに含まれているパターンを検出する

    Returns:
        パターン情報のリスト [{"column": "id", "pattern": "...", "description": "..."}, ...]
    """
    if not ids:
        return []

    patterns = []

    # パターン1: プレフィックス_連番 (例: mission_reward_1, mission_reward_2)
    # 連番部分が数字のみで、プレフィックスが共通
    match_sequential_suffix = re.match(r'^(.+?)_(\d+)$', ids[0])
    if match_sequential_suffix:
        prefix = match_sequential_suffix.group(1)
        # 全てのIDが同じプレフィックスで、連番部分が増加しているか確認
        all_match = True
        sequential_numbers = []
        for id_val in ids:
            m = re.match(r'^(.+?)_(\d+)$', id_val)
            if m and m.group(1) == prefix:
                sequential_numbers.append(int(m.group(2)))
            else:
                all_match = False
                break

        if all_match and len(sequential_numbers) > 1:
            # 連番が昇順か確認
            is_sequential = all(sequential_numbers[i] <= sequential_numbers[i+1] for i in range(len(sequential_numbers)-1))
            if is_sequential:
                # 連番の開始値を確認
                min_num = min(sequential_numbers)
                max_num = max(sequential_numbers)
                patterns.append({
                    "column": "id",
                    "pattern": f"{prefix}_[連番]",
                    "description": f"プレフィックス_{prefix} + 通算連番（範囲: {min_num}～{max_num}, 件数: {len(ids)}）"
                })

    # パターン2: プレフィックス_カテゴリ_連番 (例: mission_daily_1, mission_daily_2)
    match_category_suffix = re.match(r'^(.+?)_([a-z_]+)_(\d+)$', ids[0])
    if match_category_suffix and not patterns:  # パターン1で検出されていない場合のみ
        prefix = match_category_suffix.group(1)
        # カテゴリ別にグループ化して分析
        category_groups = defaultdict(list)
        for id_val in ids:
            m = re.match(r'^(.+?)_([a-z_]+)_(\d+)$', id_val)
            if m and m.group(1) == prefix:
                category = m.group(2)
                num = int(m.group(3))
                category_groups[category].append(num)

        # 各カテゴリごとに連番が1から始まっているか、または通算連番か判定
        if len(category_groups) > 1:
            # 複数カテゴリが存在する場合
            # 通算連番かどうかは、全カテゴリの数字が重複しないかで判定
            all_numbers = []
            for nums in category_groups.values():
                all_numbers.extend(nums)

            # 重複チェック
            has_overlap = len(all_numbers) != len(set(all_numbers))

            if has_overlap:
                # カテゴリごとに連番が振られている（通算連番ではない）
                for category, nums in category_groups.items():
                    min_num = min(nums)
                    max_num = max(nums)
                    patterns.append({
                        "column": "id",
                        "pattern": f"{prefix}_{category}_[連番]",
                        "description": f"カテゴリ別連番（カテゴリ: {category}, 範囲: {min_num}～{max_num}, 件数: {len(nums)}）"
                    })
            else:
                # 通算連番
                min_num = min(all_numbers)
                max_num = max(all_numbers)
                categories = list(category_groups.keys())
                patterns.append({
                    "column": "id",
                    "pattern": f"{prefix}_[カテゴリ]_[連番]",
                    "description": f"プレフィックス_{prefix} + カテゴリ({', '.join(categories[:3])}{'...' if len(categories) > 3 else ''}) + 通算連番（範囲: {min_num}～{max_num}, 件数: {len(ids)}）"
                })

    # パターン3: 数字のみ（単純な連番）
    if not patterns:
        all_numeric = all(id_val.isdigit() for id_val in ids)
        if all_numeric:
            numbers = [int(id_val) for id_val in ids]
            is_sequential = all(numbers[i] <= numbers[i+1] for i in range(len(numbers)-1))
            if is_sequential:
                min_num = min(numbers)
                max_num = max(numbers)
                patterns.append({
                    "column": "id",
                    "pattern": "[連番]",
                    "description": f"数字のみの通算連番（範囲: {min_num}～{max_num}, 件数: {len(ids)}）"
                })

    # パターン4: 固定プレフィックス + ゼロパディング連番 (例: MST001, MST002)
    if not patterns:
        match_padded = re.match(r'^([A-Z_]+)(\d+)$', ids[0])
        if match_padded:
            prefix = match_padded.group(1)
            # 全てのIDが同じプレフィックスで、ゼロパディング連番部分を持つか確認
            all_match = True
            numbers = []
            for id_val in ids:
                m = re.match(r'^([A-Z_]+)(\d+)$', id_val)
                if m and m.group(1) == prefix:
                    numbers.append(int(m.group(2)))
                else:
                    all_match = False
                    break

            if all_match and len(numbers) > 1:
                is_sequential = all(numbers[i] <= numbers[i+1] for i in range(len(numbers)-1))
                if is_sequential:
                    min_num = min(numbers)
                    max_num = max(numbers)
                    # ゼロパディングの桁数を確認
                    first_id_num_part = re.match(r'^([A-Z_]+)(\d+)$', ids[0]).group(2)
                    padding_width = len(first_id_num_part)
                    patterns.append({
                        "column": "id",
                        "pattern": f"{prefix}[連番({padding_width}桁ゼロパディング)]",
                        "description": f"プレフィックス_{prefix} + ゼロパディング通算連番（範囲: {min_num}～{max_num}, 件数: {len(ids)}）"
                    })

    return patterns

def main():
    masterdata_dir = Path("projects/glow-masterdata")
    csv_files = sorted(masterdata_dir.glob("*.csv"))

    results = []

    for csv_file in csv_files:
        table_name = csv_file.stem
        rows = read_csv_sample(csv_file)

        if not rows:
            continue

        # IDカラムを取得（通常は"id"だが、他のカラム名の可能性もある）
        id_column = None
        if 'id' in rows[0]:
            id_column = 'id'
        elif table_name.lower() in rows[0]:
            id_column = table_name.lower()
        else:
            # 最初のカラムをIDとみなす
            id_column = list(rows[0].keys())[0]

        # IDの値を収集
        ids = [row[id_column] for row in rows if row.get(id_column)]

        if not ids:
            continue

        # IDパターンを分析
        patterns = analyze_id_pattern(ids, table_name)

        if patterns:
            for pattern_info in patterns:
                results.append({
                    "テーブル": table_name,
                    "列": pattern_info["column"],
                    "パターン": pattern_info["pattern"],
                    "説明": pattern_info["description"]
                })
        else:
            # 通算連番ではないパターンの例も記録（参考用）
            # 最初のIDをサンプルとして記録
            sample_id = ids[0] if ids else ""
            # ただし、通算連番でない可能性が高いものはスキップ
            # （UUIDっぽい、または複雑なパターン）
            if sample_id and not re.match(r'^[a-f0-9]{8}-[a-f0-9]{4}-', sample_id):
                # 参考情報として記録
                pass

    # 結果をCSV出力
    output_file = "id_pattern_analysis.csv"
    if results:
        with open(output_file, 'w', encoding='utf-8', newline='') as f:
            writer = csv.DictWriter(f, fieldnames=["テーブル", "列", "パターン", "説明"])
            writer.writeheader()
            writer.writerows(results)

        print(f"分析完了: {len(results)}件のパターンを検出")
        print(f"結果を {output_file} に出力しました")
    else:
        print("通算連番を含むパターンは検出されませんでした")

if __name__ == "__main__":
    main()
