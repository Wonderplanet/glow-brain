#!/usr/bin/env python3
"""
イベントログインボーナス マスタデータ 差分分析スクリプト
Claude作成データと正解データを比較し、詳細なレポートを生成
"""

import csv
import json
from pathlib import Path
from typing import List, Dict, Tuple
from collections import defaultdict

# パス定義
GENERATED_DIR = Path("domain/tasks/masterdata-entry/claude-code/202601010/mission/generated")
CORRECT_DIR = Path("domain/raw-data/masterdata/released/202601010/tables")
RESULT_DIR = Path("domain/tasks/masterdata-entry/claude-code/202601010/mission/result")

# 対象ファイル
FILES = [
    "MstMissionEventDailyBonus.csv",
    "MstMissionEventDailyBonusSchedule.csv",
    "MstMissionReward.csv"
]

# イベントログインボーナスのIDパターン（正解データから抽出対象を判定）
EVENT_LOGIN_BONUS_PATTERNS = [
    "event_jig_00001_daily_bonus"
]


def read_csv_with_metadata(file_path: Path) -> Tuple[List[str], List[Dict[str, str]], bool]:
    """
    CSVファイルを読み込み、TABLE行の有無、ヘッダー、データを返す
    """
    with open(file_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    has_table_row = False
    header_idx = 0

    # TABLE行のチェック
    if lines and lines[0].startswith('TABLE,'):
        has_table_row = True
        header_idx = 1

    # ヘッダー行
    header_line = lines[header_idx].strip()
    headers = [h.strip() for h in header_line.split(',')]

    # データ行
    data = []
    reader = csv.DictReader(lines[header_idx:], fieldnames=headers)
    next(reader)  # ヘッダー行をスキップ
    for row in reader:
        if row and any(row.values()):  # 空行をスキップ
            data.append(row)

    return headers, data, has_table_row


def is_event_login_bonus_record(record: Dict[str, str]) -> bool:
    """
    レコードがイベントログインボーナスのデータかどうか判定
    """
    for key, value in record.items():
        if value:
            for pattern in EVENT_LOGIN_BONUS_PATTERNS:
                if pattern in value:
                    return True
    return False


def compare_files(filename: str) -> Dict:
    """
    ファイル単位での比較を実行
    """
    result = {
        'filename': filename,
        'claude_file': str(GENERATED_DIR / filename),
        'correct_file': str(CORRECT_DIR / filename),
        'exists_in_both': False,
        'structure_diff': {},
        'data_diff': {},
        'accuracy': {}
    }

    claude_path = GENERATED_DIR / filename
    correct_path = CORRECT_DIR / filename

    # ファイル存在チェック
    if not claude_path.exists():
        result['error'] = f"Claude作成データが存在しません: {claude_path}"
        return result
    if not correct_path.exists():
        result['error'] = f"正解データが存在しません: {correct_path}"
        return result

    result['exists_in_both'] = True

    # ファイル読み込み
    claude_headers, claude_data, claude_has_table = read_csv_with_metadata(claude_path)
    correct_headers, correct_data, correct_has_table = read_csv_with_metadata(correct_path)

    # MstMissionRewardの場合、正解データからイベントログインボーナス部分のみ抽出
    if filename == "MstMissionReward.csv":
        correct_data_filtered = [r for r in correct_data if is_event_login_bonus_record(r)]
        result['correct_total_records'] = len(correct_data)
        result['correct_filtered_records'] = len(correct_data_filtered)
        correct_data = correct_data_filtered

    # 構造の比較
    result['structure_diff'] = {
        'claude_has_table_row': claude_has_table,
        'correct_has_table_row': correct_has_table,
        'table_row_match': claude_has_table == correct_has_table,
        'claude_columns': claude_headers,
        'correct_columns': correct_headers,
        'columns_match': claude_headers == correct_headers,
        'claude_record_count': len(claude_data),
        'correct_record_count': len(correct_data)
    }

    # データの詳細比較
    data_comparison = compare_data_content(claude_data, correct_data, claude_headers, filename)
    result['data_diff'] = data_comparison

    # 正解率の計算
    result['accuracy'] = calculate_accuracy(data_comparison, len(claude_data), len(correct_data))

    return result


def normalize_value(value: str) -> str:
    """
    値を正規化（比較用）
    - クォートを除去
    - 空白をトリム
    """
    if not value:
        return ""
    return value.strip().strip('"').strip("'")


def compare_data_content(claude_data: List[Dict], correct_data: List[Dict], headers: List[str], filename: str) -> Dict:
    """
    データ内容の詳細比較
    """
    comparison = {
        'field_differences': defaultdict(list),
        'row_by_row_diff': [],
        'critical_columns': [],
        'non_critical_columns': ['備考']
    }

    # 重要カラムの定義（テーブルごとに異なる）
    if filename == "MstMissionEventDailyBonus.csv":
        comparison['critical_columns'] = ['id', 'release_key', 'mst_mission_event_daily_bonus_schedule_id',
                                          'login_day_count', 'mst_mission_reward_group_id', 'sort_order']
    elif filename == "MstMissionEventDailyBonusSchedule.csv":
        comparison['critical_columns'] = ['id', 'release_key', 'mst_event_id', 'start_at', 'end_at']
    elif filename == "MstMissionReward.csv":
        comparison['critical_columns'] = ['id', 'release_key', 'group_id', 'resource_type',
                                          'resource_id', 'resource_amount', 'sort_order']

    # 行ごとの比較
    max_rows = max(len(claude_data), len(correct_data))

    for i in range(max_rows):
        row_diff = {
            'row_number': i + 1,
            'differences': {}
        }

        claude_row = claude_data[i] if i < len(claude_data) else None
        correct_row = correct_data[i] if i < len(correct_data) else None

        if claude_row is None:
            row_diff['status'] = 'missing_in_claude'
            row_diff['correct_row'] = correct_row
        elif correct_row is None:
            row_diff['status'] = 'extra_in_claude'
            row_diff['claude_row'] = claude_row
        else:
            row_diff['status'] = 'exists_in_both'

            # カラムごとの比較
            for header in headers:
                claude_val = normalize_value(claude_row.get(header, ''))
                correct_val = normalize_value(correct_row.get(header, ''))

                if claude_val != correct_val:
                    is_critical = header in comparison['critical_columns']
                    row_diff['differences'][header] = {
                        'claude': claude_row.get(header, ''),
                        'correct': correct_row.get(header, ''),
                        'is_critical': is_critical
                    }

                    comparison['field_differences'][header].append({
                        'row': i + 1,
                        'claude': claude_row.get(header, ''),
                        'correct': correct_row.get(header, ''),
                        'is_critical': is_critical
                    })

        if row_diff['differences'] or row_diff['status'] != 'exists_in_both':
            comparison['row_by_row_diff'].append(row_diff)

    return comparison


def calculate_accuracy(data_comparison: Dict, claude_count: int, correct_count: int) -> Dict:
    """
    正解率を計算
    """
    accuracy = {
        'record_count_match': claude_count == correct_count,
        'record_count_accuracy': f"{claude_count}/{correct_count}",
    }

    # 重要カラムの一致率
    critical_diffs = []
    non_critical_diffs = []

    for field, diffs in data_comparison['field_differences'].items():
        if diffs:
            if diffs[0]['is_critical']:
                critical_diffs.extend(diffs)
            else:
                non_critical_diffs.extend(diffs)

    total_critical_cells = claude_count * len(data_comparison['critical_columns'])
    total_non_critical_cells = claude_count * len(data_comparison['non_critical_columns'])

    critical_correct_cells = total_critical_cells - len(critical_diffs)
    non_critical_correct_cells = total_non_critical_cells - len(non_critical_diffs)

    accuracy['critical_fields'] = {
        'correct_cells': critical_correct_cells,
        'total_cells': total_critical_cells,
        'accuracy_rate': f"{critical_correct_cells / total_critical_cells * 100:.2f}%" if total_critical_cells > 0 else "N/A",
        'error_count': len(critical_diffs)
    }

    accuracy['non_critical_fields'] = {
        'correct_cells': non_critical_correct_cells,
        'total_cells': total_non_critical_cells,
        'accuracy_rate': f"{non_critical_correct_cells / total_non_critical_cells * 100:.2f}%" if total_non_critical_cells > 0 else "N/A",
        'error_count': len(non_critical_diffs)
    }

    # 総合正解率
    total_cells = total_critical_cells + total_non_critical_cells
    total_correct = critical_correct_cells + non_critical_correct_cells

    accuracy['overall'] = {
        'correct_cells': total_correct,
        'total_cells': total_cells,
        'accuracy_rate': f"{total_correct / total_cells * 100:.2f}%" if total_cells > 0 else "N/A"
    }

    return accuracy


def generate_markdown_report(results: List[Dict]) -> str:
    """
    Markdown形式のレポートを生成
    """
    report = []
    report.append("# イベントログインボーナス マスタデータ 差分レポート\n")
    report.append(f"**生成日時**: {Path(__file__).stat().st_mtime}\n")
    report.append("---\n")

    # サマリー
    report.append("## 📊 サマリー\n")
    report.append("| ファイル名 | レコード数 | 重要フィールド正解率 | 総合正解率 | 状態 |")
    report.append("|-----------|-----------|-------------------|-----------|------|")

    for result in results:
        if not result['exists_in_both']:
            report.append(f"| {result['filename']} | - | - | - | ❌ エラー |")
        else:
            acc = result['accuracy']
            critical_rate = acc['critical_fields']['accuracy_rate']
            overall_rate = acc['overall']['accuracy_rate']
            status = "✅ 完全一致" if critical_rate == "100.00%" and overall_rate == "100.00%" else "⚠️ 差分あり"

            report.append(f"| {result['filename']} | {result['structure_diff']['claude_record_count']}/{result['structure_diff']['correct_record_count']} | {critical_rate} | {overall_rate} | {status} |")

    report.append("")

    # 各ファイルの詳細
    for result in results:
        report.append(f"## 📄 {result['filename']}\n")

        if not result['exists_in_both']:
            report.append(f"**エラー**: {result.get('error', '不明なエラー')}\n")
            continue

        # 構造の差分
        report.append("### 構造の比較\n")
        struct = result['structure_diff']

        report.append("| 項目 | Claude作成 | 正解データ | 一致 |")
        report.append("|-----|----------|----------|------|")
        report.append(f"| TABLE行 | {'あり' if struct['claude_has_table_row'] else 'なし'} | {'あり' if struct['correct_has_table_row'] else 'なし'} | {'✅' if struct['table_row_match'] else '❌'} |")
        report.append(f"| レコード数 | {struct['claude_record_count']} | {struct['correct_record_count']} | {'✅' if struct['claude_record_count'] == struct['correct_record_count'] else '❌'} |")
        report.append(f"| カラム構造 | {len(struct['claude_columns'])}列 | {len(struct['correct_columns'])}列 | {'✅' if struct['columns_match'] else '❌'} |")
        report.append("")

        # MstMissionRewardの場合、フィルタ情報を表示
        if result['filename'] == "MstMissionReward.csv" and 'correct_total_records' in result:
            report.append(f"**注**: 正解データには全{result['correct_total_records']}レコードが含まれていますが、イベントログインボーナス関連の{result['correct_filtered_records']}レコードのみを比較対象としています。\n")

        # 正解率
        report.append("### 正解率\n")
        acc = result['accuracy']

        report.append("#### 重要フィールド")
        report.append(f"- **正解率**: {acc['critical_fields']['accuracy_rate']}")
        report.append(f"- 正解セル数: {acc['critical_fields']['correct_cells']} / {acc['critical_fields']['total_cells']}")
        report.append(f"- エラー数: {acc['critical_fields']['error_count']}")
        report.append("")

        report.append("#### 非重要フィールド（備考など）")
        report.append(f"- **正解率**: {acc['non_critical_fields']['accuracy_rate']}")
        report.append(f"- 正解セル数: {acc['non_critical_fields']['correct_cells']} / {acc['non_critical_fields']['total_cells']}")
        report.append(f"- エラー数: {acc['non_critical_fields']['error_count']}")
        report.append("")

        report.append("#### 総合")
        report.append(f"- **総合正解率**: {acc['overall']['accuracy_rate']}")
        report.append(f"- 正解セル数: {acc['overall']['correct_cells']} / {acc['overall']['total_cells']}")
        report.append("")

        # データの差分詳細
        data_diff = result['data_diff']

        if data_diff['field_differences']:
            report.append("### フィールド別差分サマリー\n")
            report.append("| フィールド名 | 差分数 | 重要度 |")
            report.append("|------------|-------|-------|")

            for field, diffs in sorted(data_diff['field_differences'].items()):
                is_critical = "🔴 重要" if diffs[0]['is_critical'] else "🟡 非重要"
                report.append(f"| {field} | {len(diffs)} | {is_critical} |")

            report.append("")

            # 差分の詳細
            report.append("### 差分の詳細\n")

            for field, diffs in sorted(data_diff['field_differences'].items()):
                is_critical = "🔴 重要" if diffs[0]['is_critical'] else "🟡 非重要"
                report.append(f"#### {field} ({is_critical})\n")

                if len(diffs) <= 5:
                    # 差分が少ない場合は全て表示
                    report.append("| 行 | Claude作成 | 正解データ |")
                    report.append("|----|-----------|----------|")
                    for diff in diffs:
                        report.append(f"| {diff['row']} | `{diff['claude']}` | `{diff['correct']}` |")
                else:
                    # 差分が多い場合は最初の3つと最後の2つを表示
                    report.append("| 行 | Claude作成 | 正解データ |")
                    report.append("|----|-----------|----------|")
                    for diff in diffs[:3]:
                        report.append(f"| {diff['row']} | `{diff['claude']}` | `{diff['correct']}` |")
                    report.append(f"| ... | ... | ... |")
                    for diff in diffs[-2:]:
                        report.append(f"| {diff['row']} | `{diff['claude']}` | `{diff['correct']}` |")
                    report.append(f"\n**全{len(diffs)}件の差分**\n")

                report.append("")
        else:
            report.append("### ✅ データの差分なし\n")
            report.append("全てのデータが正解データと一致しています。\n")

    # 総括
    report.append("---\n")
    report.append("## 📝 総括\n")

    all_perfect = all(
        r['exists_in_both'] and
        r['accuracy']['critical_fields']['accuracy_rate'] == "100.00%"
        for r in results
    )

    if all_perfect:
        report.append("### ✅ 完全一致")
        report.append("全てのファイルで重要フィールドが100%一致しています。Claude Codeは正確にマスタデータを作成しました。\n")
    else:
        report.append("### ⚠️ 差分が検出されました\n")
        report.append("以下の点に注意が必要です：\n")

        # 共通の問題点を抽出
        common_issues = []

        table_row_issue = all(
            r['exists_in_both'] and
            r['structure_diff']['claude_has_table_row'] and
            not r['structure_diff']['correct_has_table_row']
            for r in results
        )

        if table_row_issue:
            common_issues.append("- **TABLE行**: 全てのファイルでClaude作成データにのみTABLE行が存在します。これはCSVフォーマットの違いによるもので、データの実質的な内容には影響しません。")

        # MstMissionRewardのID問題
        for r in results:
            if r['filename'] == "MstMissionReward.csv" and r['exists_in_both']:
                if 'id' in r['data_diff']['field_differences']:
                    common_issues.append("- **MstMissionReward.csvのID**: Claude作成データは説明的なID（`mission_reward_event_jig_00001_daily_bonus_01_1`）を使用していますが、正解データは連番ID（`mission_reward_463`）を使用しています。これはID採番ルールの違いによるものです。")

        # 備考欄の問題
        for r in results:
            if r['exists_in_both'] and '備考' in r['data_diff']['field_differences']:
                common_issues.append(f"- **備考欄（{r['filename']}）**: 正解データには具体的な説明が入っていますが、Claude作成データは空です。これは非重要フィールドであり、データの機能には影響しません。")

        for issue in common_issues:
            report.append(issue)

        report.append("")

    return "\n".join(report)


def main():
    """
    メイン処理
    """
    print("イベントログインボーナス マスタデータ 差分分析を開始します...")

    # 各ファイルの比較
    results = []
    for filename in FILES:
        print(f"  - {filename} を比較中...")
        result = compare_files(filename)
        results.append(result)

    # JSONレポート生成
    json_path = RESULT_DIR / "差分分析結果.json"
    with open(json_path, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    print(f"✅ JSONレポートを生成しました: {json_path}")

    # Markdownレポート生成
    markdown_report = generate_markdown_report(results)
    markdown_path = RESULT_DIR / "差分レポート.md"
    with open(markdown_path, 'w', encoding='utf-8') as f:
        f.write(markdown_report)
    print(f"✅ Markdownレポートを生成しました: {markdown_path}")

    print("\n差分分析が完了しました！")


if __name__ == "__main__":
    main()
