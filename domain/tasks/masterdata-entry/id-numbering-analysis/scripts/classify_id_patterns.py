#!/usr/bin/env python3
"""
ID採番パターンを分類するスクリプト
通算連番を「純粋な通算連番」と「バージョン付き通算連番」に分類
"""

import csv
import re

def classify_pattern(pattern: str) -> str:
    """
    パターンを分類
    - 純粋な通算連番: プレフィックスにバージョン番号が含まれていない
    - バージョン付き通算連番: プレフィックスに_数字_のようなバージョン番号が含まれている
    - 数字のみ: プレフィックスがない
    """
    if pattern == "[連番]":
        return "数字のみの通算連番"

    # プレフィックスの末尾に_数字_があるかチェック
    # 例: comeback_1_[連番], achievement_2_[連番], premium_pass_01_effect_[連番]
    match = re.search(r'_(\d+)_\[連番\]', pattern)
    if match:
        return "バージョン付き通算連番"

    # 純粋な通算連番
    return "純粋な通算連番（プレフィックス）"

def main():
    input_file = "id_pattern_analysis_filtered.csv"
    output_file = "id_pattern_analysis_classified.csv"

    with open(input_file, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        rows = list(reader)

    # 分類を追加
    for row in rows:
        pattern = row['パターン']
        classification = classify_pattern(pattern)
        row['分類'] = classification

    # 分類別にソート
    classification_order = {
        "数字のみの通算連番": 1,
        "純粋な通算連番（プレフィックス）": 2,
        "バージョン付き通算連番": 3
    }
    rows.sort(key=lambda r: (classification_order.get(r['分類'], 99), r['テーブル']))

    # 結果をCSV出力
    with open(output_file, 'w', encoding='utf-8', newline='') as f:
        fieldnames = ['テーブル', '列', 'パターン', '説明', '分類']
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)

    # 統計情報を出力
    from collections import Counter
    classification_counts = Counter(row['分類'] for row in rows)

    print("=== ID採番パターン分類結果 ===")
    print()
    for classification, count in sorted(classification_counts.items(), key=lambda x: classification_order.get(x[0], 99)):
        print(f"{classification}: {count}件")
    print()
    print(f"結果を {output_file} に出力しました")

if __name__ == "__main__":
    main()
