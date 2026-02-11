#!/usr/bin/env python3
"""
総合差分レポート作成スクリプト
diff_summary.jsonから統計とレポートを生成
"""

import json
from pathlib import Path
from typing import Dict, List, Any

def calculate_statistics(diff_results: List[Dict[str, Any]]) -> Dict[str, Any]:
    """統計情報を計算"""
    total_files = len(diff_results)
    files_with_diff = sum(1 for r in diff_results if r['has_diff'])
    files_without_diff = total_files - files_with_diff
    
    total_added = sum(len(r['added_rows']) for r in diff_results)
    total_deleted = sum(len(r['deleted_rows']) for r in diff_results)
    total_modified = sum(len(r['modified_rows']) for r in diff_results)
    total_identical = sum(r['identical_rows'] for r in diff_results)
    
    total_generated_rows = sum(r['total_generated'] for r in diff_results)
    total_correct_rows = sum(r['total_correct'] for r in diff_results)
    
    # 精度計算
    total_diff_count = total_added + total_deleted + total_modified
    accuracy = (1 - (total_diff_count / max(total_correct_rows, 1))) * 100 if total_correct_rows > 0 else 0
    
    return {
        'total_files': total_files,
        'files_with_diff': files_with_diff,
        'files_without_diff': files_without_diff,
        'total_added_rows': total_added,
        'total_deleted_rows': total_deleted,
        'total_modified_rows': total_modified,
        'total_identical_rows': total_identical,
        'total_generated_rows': total_generated_rows,
        'total_correct_rows': total_correct_rows,
        'total_diff_count': total_diff_count,
        'accuracy_percentage': accuracy
    }

def create_summary_report(diff_results: List[Dict[str, Any]], stats: Dict[str, Any]) -> str:
    """総合レポートを作成"""
    lines = []
    
    # タイトル
    lines.append("# マスタデータ生成結果 精度評価レポート")
    lines.append("")
    lines.append("リリースキー: **202601010**")
    lines.append("")
    
    # エグゼクティブサマリー
    lines.append("## エグゼクティブサマリー")
    lines.append("")
    lines.append(f"masterdata-from-bizops-allスキルを使用して生成したマスタデータの精度評価を実施しました。")
    lines.append(f"**25個のCSVファイル**を対象に、生成結果と正解データの差分を比較しました。")
    lines.append("")
    lines.append(f"### 主要な結果")
    lines.append(f"- **全体精度**: {stats['accuracy_percentage']:.2f}%")
    lines.append(f"- **完全一致ファイル**: {stats['files_without_diff']}/{stats['total_files']} ({stats['files_without_diff']/stats['total_files']*100:.1f}%)")
    lines.append(f"- **差分ありファイル**: {stats['files_with_diff']}/{stats['total_files']} ({stats['files_with_diff']/stats['total_files']*100:.1f}%)")
    lines.append(f"- **総行数（正解）**: {stats['total_correct_rows']:,}")
    lines.append(f"- **総差分行数**: {stats['total_diff_count']:,} (追加: {stats['total_added_rows']}, 削除: {stats['total_deleted_rows']}, 変更: {stats['total_modified_rows']})")
    lines.append("")
    
    # 詳細統計
    lines.append("## 詳細統計")
    lines.append("")
    lines.append("| 項目 | 値 |")
    lines.append("|------|------|")
    lines.append(f"| 比較対象ファイル数 | {stats['total_files']} |")
    lines.append(f"| 差分のないファイル数 | {stats['files_without_diff']} |")
    lines.append(f"| 差分のあるファイル数 | {stats['files_with_diff']} |")
    lines.append(f"| 総行数（生成結果） | {stats['total_generated_rows']:,} |")
    lines.append(f"| 総行数（正解データ） | {stats['total_correct_rows']:,} |")
    lines.append(f"| 一致した行数 | {stats['total_identical_rows']:,} |")
    lines.append(f"| 追加された行数 | {stats['total_added_rows']:,} |")
    lines.append(f"| 削除された行数 | {stats['total_deleted_rows']:,} |")
    lines.append(f"| 変更された行数 | {stats['total_modified_rows']:,} |")
    lines.append(f"| 全体精度 | {stats['accuracy_percentage']:.2f}% |")
    lines.append("")
    
    # ファイル別の差分統計
    lines.append("## ファイル別の差分統計")
    lines.append("")
    lines.append("| ファイル名 | 差分 | 生成行数 | 正解行数 | 追加 | 削除 | 変更 | 一致 |")
    lines.append("|-----------|------|---------|---------|------|------|------|------|")
    
    for result in sorted(diff_results, key=lambda x: x['filename']):
        diff_icon = "✗" if result['has_diff'] else "✓"
        lines.append(
            f"| {result['filename']} | {diff_icon} | {result['total_generated']} | "
            f"{result['total_correct']} | {len(result['added_rows'])} | "
            f"{len(result['deleted_rows'])} | {len(result['modified_rows'])} | "
            f"{result['identical_rows']} |"
        )
    
    lines.append("")
    
    # 差分が多いテーブルトップ10
    lines.append("## 差分が多いテーブル トップ10")
    lines.append("")
    
    # 総差分数でソート
    diff_sorted = sorted(
        diff_results,
        key=lambda x: len(x['added_rows']) + len(x['deleted_rows']) + len(x['modified_rows']),
        reverse=True
    )[:10]
    
    lines.append("| 順位 | ファイル名 | 総差分数 | 追加 | 削除 | 変更 |")
    lines.append("|------|-----------|---------|------|------|------|")
    
    for i, result in enumerate(diff_sorted, 1):
        total_diff = len(result['added_rows']) + len(result['deleted_rows']) + len(result['modified_rows'])
        if total_diff > 0:
            lines.append(
                f"| {i} | {result['filename']} | {total_diff} | "
                f"{len(result['added_rows'])} | {len(result['deleted_rows'])} | "
                f"{len(result['modified_rows'])} |"
            )
    
    lines.append("")
    
    # 完全一致ファイル
    lines.append("## 完全一致ファイル")
    lines.append("")
    perfect_matches = [r for r in diff_results if not r['has_diff']]
    
    if perfect_matches:
        lines.append("以下のファイルは正解データと完全に一致しています:")
        lines.append("")
        for result in sorted(perfect_matches, key=lambda x: x['filename']):
            lines.append(f"- ✓ {result['filename']}")
    else:
        lines.append("完全一致のファイルはありません。")
    
    lines.append("")
    
    # 差分の傾向分析
    lines.append("## 差分の傾向分析")
    lines.append("")
    
    # 追加/削除/変更の傾向
    if stats['total_added_rows'] > 0:
        lines.append(f"### 追加された行 ({stats['total_added_rows']}件)")
        lines.append("")
        lines.append("正解データに存在するが、生成結果には含まれていない行があります。")
        lines.append("これは運営仕様書からの抽出漏れ、またはデータ生成ロジックの不足を示している可能性があります。")
        lines.append("")
    
    if stats['total_deleted_rows'] > 0:
        lines.append(f"### 削除された行 ({stats['total_deleted_rows']}件)")
        lines.append("")
        lines.append("生成結果に含まれているが、正解データには存在しない行があります。")
        lines.append("これは不要なデータの生成、または運営仕様書の解釈誤りを示している可能性があります。")
        lines.append("")
    
    if stats['total_modified_rows'] > 0:
        lines.append(f"### 変更された行 ({stats['total_modified_rows']}件)")
        lines.append("")
        lines.append("同じIDを持つ行が存在しますが、カラムの値が異なります。")
        lines.append("これはデータ変換ロジックの誤り、型変換の問題、またはデフォルト値の設定誤りを示している可能性があります。")
        lines.append("")
    
    # 改善の方向性
    lines.append("## 改善の方向性")
    lines.append("")
    lines.append("### 短期的な改善")
    lines.append("")
    lines.append("1. **差分が多いテーブルの優先的な調査**")
    lines.append("   - 上記トップ10のテーブルについて、個別の差分詳細レポートを確認")
    lines.append("   - 差分の原因を特定し、運営仕様書の読み取りロジックやデータ生成ロジックを修正")
    lines.append("")
    lines.append("2. **エラーケースの修正**")
    lines.append("   - ヘッダー不一致やエラーが発生したファイルを優先的に修正")
    lines.append("")
    lines.append("3. **変更された行の原因調査**")
    lines.append("   - 型変換、デフォルト値、データフォーマットの問題を調査")
    lines.append("")
    lines.append("### 中長期的な改善")
    lines.append("")
    lines.append("1. **自動検証の強化**")
    lines.append("   - masterdata-csv-validatorスキルの活用")
    lines.append("   - スキーマとの整合性チェックの自動化")
    lines.append("")
    lines.append("2. **運営仕様書の標準化**")
    lines.append("   - 仕様書のフォーマットを統一し、機械的な読み取りを容易にする")
    lines.append("")
    lines.append("3. **テストケースの拡充**")
    lines.append("   - 今回の検証データセットをリグレッションテストに活用")
    lines.append("")
    
    # 個別レポートへのリンク
    lines.append("## 個別ファイルの詳細レポート")
    lines.append("")
    lines.append("各ファイルの詳細な差分は、以下のレポートを参照してください:")
    lines.append("")
    
    for result in sorted(diff_results, key=lambda x: x['filename']):
        if result['has_diff']:
            report_filename = result['filename'].replace('.csv', '_diff.md')
            lines.append(f"- [{result['filename']}](./{report_filename})")
    
    lines.append("")
    
    return '\n'.join(lines)

def main():
    # diff_summary.jsonを読み込み
    summary_path = Path('/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010/diffs/diff_summary.json')
    
    with open(summary_path, 'r', encoding='utf-8') as f:
        diff_results = json.load(f)
    
    # 統計情報を計算
    stats = calculate_statistics(diff_results)
    
    # レポートを作成
    report = create_summary_report(diff_results, stats)
    
    # レポートを保存
    report_path = summary_path.parent / 'summary_report.md'
    with open(report_path, 'w', encoding='utf-8') as f:
        f.write(report)
    
    print("=" * 80)
    print("総合差分レポート作成完了！")
    print("=" * 80)
    print(f"\nレポートパス: {report_path}")
    print(f"\n主要な結果:")
    print(f"  - 全体精度: {stats['accuracy_percentage']:.2f}%")
    print(f"  - 完全一致: {stats['files_without_diff']}/{stats['total_files']} ファイル")
    print(f"  - 差分あり: {stats['files_with_diff']}/{stats['total_files']} ファイル")
    print(f"  - 総差分数: {stats['total_diff_count']:,} 行")
    print()

if __name__ == '__main__':
    main()
