#!/usr/bin/env python3
import json
from pathlib import Path
from typing import Dict, List, Any

def calculate_statistics(diff_results: List[Dict[str, Any]]) -> Dict[str, Any]:
    total_files = len(diff_results)
    files_with_diff = sum(1 for r in diff_results if r['has_diff'])
    files_without_diff = total_files - files_with_diff
    total_added = sum(len(r['added_rows']) for r in diff_results)
    total_deleted = sum(len(r['deleted_rows']) for r in diff_results)
    total_modified = sum(len(r['modified_rows']) for r in diff_results)
    total_identical = sum(r['identical_rows'] for r in diff_results)
    total_generated_rows = sum(r['total_generated'] for r in diff_results)
    total_correct_rows = sum(r['total_correct'] for r in diff_results)
    total_diff_count = total_added + total_deleted + total_modified
    accuracy = (1 - (total_diff_count / max(total_correct_rows, 1))) * 100 if total_correct_rows > 0 else 0
    return {
        'total_files': total_files, 'files_with_diff': files_with_diff, 'files_without_diff': files_without_diff,
        'total_added_rows': total_added, 'total_deleted_rows': total_deleted, 'total_modified_rows': total_modified,
        'total_identical_rows': total_identical, 'total_generated_rows': total_generated_rows,
        'total_correct_rows': total_correct_rows, 'total_diff_count': total_diff_count,
        'accuracy_percentage': accuracy
    }

with open('diff_summary.json', 'r') as f:
    diff_results = json.load(f)
with open('file_level_diff.json', 'r') as f:
    file_level_diff = json.load(f)

stats = calculate_statistics(diff_results)

lines = ["# マスタデータ生成結果 精度評価レポート（完全版）", "", "リリースキー: **202601010**", ""]
lines.extend(["## エグゼクティブサマリー", "", "masterdata-from-bizops-allスキルを使用して生成したマスタデータの精度評価を実施しました。", ""])
lines.extend(["### 🚨 重要な発見", "",
    f"**正解データには{file_level_diff['total_correct_files']}個のCSVファイルが存在しますが、生成結果は{file_level_diff['total_generated_files']}個のみでした。**",
    "", f"- **ファイル生成率**: {file_level_diff['file_generation_rate']:.1f}%",
    f"- **生成されていないファイル数**: {len(file_level_diff['only_in_correct'])}個", ""])
lines.extend(["### 主要な結果（生成された25ファイルの分析）", "",
    f"- **完全一致ファイル**: {stats['files_without_diff']}/{stats['total_files']} ({stats['files_without_diff']/stats['total_files']*100:.1f}%)",
    f"- **差分ありファイル**: {stats['files_with_diff']}/{stats['total_files']} ({stats['files_with_diff']/stats['total_files']*100:.1f}%)",
    f"- **総行数（正解）**: {stats['total_correct_rows']:,}",
    f"- **総差分行数**: {stats['total_diff_count']:,} (追加: {stats['total_added_rows']}, 削除: {stats['total_deleted_rows']}, 変更: {stats['total_modified_rows']})", ""])

lines.extend(["## ファイルレベルの差分", "", "### 統計", "", "| 項目 | 値 |", "|------|------|",
    f"| 正解データのファイル数 | {file_level_diff['total_correct_files']} |",
    f"| 生成結果のファイル数 | {file_level_diff['total_generated_files']} |",
    f"| 両方に存在するファイル数 | {file_level_diff['files_in_both']} |",
    f"| 生成されていないファイル数 | {len(file_level_diff['only_in_correct'])} |",
    f"| 余分に生成されたファイル数 | {len(file_level_diff['only_in_generated'])} |",
    f"| ファイル生成率 | {file_level_diff['file_generation_rate']:.1f}% |", ""])

if file_level_diff['only_in_correct']:
    lines.extend([f"### 生成されていないファイル（{len(file_level_diff['only_in_correct'])}個）", "", "<details>", "<summary>クリックして展開</summary>", ""])
    categories = {'Unit': [], 'Enemy': [], 'Ability': [], 'Attack': [], 'Gacha': [], 'Mission': [], 'InGame': [], 'Artwork': [], 'Other': []}
    for fname in file_level_diff['only_in_correct']:
        categorized = False
        for cat in categories.keys():
            if cat.lower() in fname.lower():
                categories[cat].append(fname)
                categorized = True
                break
        if not categorized:
            categories['Other'].append(fname)
    for cat, files in categories.items():
        if files:
            lines.append(f"**{cat}関連 ({len(files)}個):**")
            for fname in sorted(files):
                lines.append(f"- {fname}")
            lines.append("")
    lines.extend(["</details>", ""])

print('\n'.join(lines[:50]))
with open('summary_report.md', 'w') as f:
    f.write('\n'.join(lines))
print(f"\n完了: summary_report.md 更新（最初の50行を表示）")
