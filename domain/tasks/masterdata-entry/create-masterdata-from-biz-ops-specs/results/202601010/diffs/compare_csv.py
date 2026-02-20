#!/usr/bin/env python3
"""
CSVファイルの差分比較スクリプト
生成結果と正解データを比較し、詳細な差分レポートを作成
"""

import json
import csv
import os
from pathlib import Path
from typing import Dict, List, Tuple, Any
from collections import defaultdict

def read_csv_as_dict(filepath: str) -> Tuple[List[str], List[Dict[str, str]]]:
    """CSVファイルを読み込み、ヘッダーと行のリストを返す"""
    with open(filepath, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        headers = reader.fieldnames or []
        rows = list(reader)
    return headers, rows

def create_row_key(row: Dict[str, str], id_columns: List[str]) -> str:
    """行の一意キーを生成（複数のID列に対応）"""
    key_parts = []
    for col in id_columns:
        key_parts.append(str(row.get(col, '')))
    return '|'.join(key_parts)

def find_id_columns(headers: List[str]) -> List[str]:
    """ID列を推測（idで終わる列、または最初の列）"""
    id_cols = [h for h in headers if h.lower().endswith('id') or h.lower() == 'id']
    if not id_cols and headers:
        id_cols = [headers[0]]
    return id_cols

def compare_csvs(generated_path: str, correct_path: str) -> Dict[str, Any]:
    """2つのCSVファイルを比較"""
    result = {
        'filename': Path(generated_path).name,
        'generated_path': generated_path,
        'correct_path': correct_path,
        'has_diff': False,
        'added_rows': [],
        'deleted_rows': [],
        'modified_rows': [],
        'identical_rows': 0,
        'total_generated': 0,
        'total_correct': 0,
        'errors': []
    }

    try:
        gen_headers, gen_rows = read_csv_as_dict(generated_path)
        cor_headers, cor_rows = read_csv_as_dict(correct_path)

        result['total_generated'] = len(gen_rows)
        result['total_correct'] = len(cor_rows)

        if set(gen_headers) != set(cor_headers):
            result['errors'].append({
                'type': 'header_mismatch',
                'generated_headers': gen_headers,
                'correct_headers': cor_headers,
                'missing_in_generated': list(set(cor_headers) - set(gen_headers)),
                'extra_in_generated': list(set(gen_headers) - set(cor_headers))
            })

        id_columns = find_id_columns(cor_headers)
        gen_rows_dict = {create_row_key(row, id_columns): row for row in gen_rows}
        cor_rows_dict = {create_row_key(row, id_columns): row for row in cor_rows}

        for key, row in cor_rows_dict.items():
            if key not in gen_rows_dict:
                result['added_rows'].append({'key': key, 'data': row})

        for key, row in gen_rows_dict.items():
            if key not in cor_rows_dict:
                result['deleted_rows'].append({'key': key, 'data': row})

        for key in set(gen_rows_dict.keys()) & set(cor_rows_dict.keys()):
            gen_row = gen_rows_dict[key]
            cor_row = cor_rows_dict[key]
            differences = {}
            all_columns = set(gen_row.keys()) | set(cor_row.keys())

            for col in all_columns:
                gen_val = gen_row.get(col, '')
                cor_val = cor_row.get(col, '')
                gen_normalized = gen_val if gen_val else ''
                cor_normalized = cor_val if cor_val else ''

                if gen_normalized != cor_normalized:
                    differences[col] = {'generated': gen_val, 'correct': cor_val}

            if differences:
                result['modified_rows'].append({
                    'key': key,
                    'id_columns': {col: cor_row.get(col) for col in id_columns},
                    'differences': differences
                })
            else:
                result['identical_rows'] += 1

        result['has_diff'] = bool(
            result['added_rows'] or
            result['deleted_rows'] or
            result['modified_rows'] or
            result['errors']
        )

    except Exception as e:
        result['errors'].append({'type': 'comparison_error', 'message': str(e)})
        result['has_diff'] = True

    return result

def create_diff_report(diff_result: Dict[str, Any]) -> str:
    """差分結果からMarkdownレポートを生成"""
    lines = [f"# {diff_result['filename']} - 差分詳細", "", "## サマリー", ""]
    lines.append(f"- **生成結果の行数**: {diff_result['total_generated']}")
    lines.append(f"- **正解データの行数**: {diff_result['total_correct']}")
    lines.append(f"- **一致した行数**: {diff_result['identical_rows']}")
    lines.append(f"- **追加された行数**: {len(diff_result['added_rows'])} (正解にあるが生成結果にない)")
    lines.append(f"- **削除された行数**: {len(diff_result['deleted_rows'])} (生成結果にあるが正解にない)")
    lines.append(f"- **変更された行数**: {len(diff_result['modified_rows'])}")
    lines.append(f"- **差分の有無**: {'**あり**' if diff_result['has_diff'] else 'なし'}")
    lines.append("")

    if diff_result['errors']:
        lines.append("## エラー")
        lines.append("")
        for i, error in enumerate(diff_result['errors'], 1):
            lines.append(f"### エラー {i}: {error.get('type', 'unknown')}")
            if error['type'] == 'header_mismatch':
                lines.append("")
                lines.append("**ヘッダーの不一致:**")
                if error['missing_in_generated']:
                    lines.append(f"- 生成結果に不足: {', '.join(error['missing_in_generated'])}")
                if error['extra_in_generated']:
                    lines.append(f"- 生成結果に余分: {', '.join(error['extra_in_generated'])}")
            else:
                lines.append(f"```\n{error.get('message', '')}\n```")
            lines.append("")

    if diff_result['added_rows']:
        lines.append("## 追加された行（正解にあるが生成結果にない）")
        lines.append("")
        for i, row_info in enumerate(diff_result['added_rows'][:10], 1):
            lines.append(f"### 追加 {i}: Key={row_info['key']}")
            lines.append("```json")
            lines.append(json.dumps(row_info['data'], ensure_ascii=False, indent=2))
            lines.append("```")
            lines.append("")
        if len(diff_result['added_rows']) > 10:
            lines.append(f"*（他 {len(diff_result['added_rows']) - 10} 件）*")
            lines.append("")

    if diff_result['deleted_rows']:
        lines.append("## 削除された行（生成結果にあるが正解にない）")
        lines.append("")
        for i, row_info in enumerate(diff_result['deleted_rows'][:10], 1):
            lines.append(f"### 削除 {i}: Key={row_info['key']}")
            lines.append("```json")
            lines.append(json.dumps(row_info['data'], ensure_ascii=False, indent=2))
            lines.append("```")
            lines.append("")
        if len(diff_result['deleted_rows']) > 10:
            lines.append(f"*（他 {len(diff_result['deleted_rows']) - 10} 件）*")
            lines.append("")

    if diff_result['modified_rows']:
        lines.append("## 変更された行")
        lines.append("")
        for i, row_info in enumerate(diff_result['modified_rows'][:10], 1):
            lines.append(f"### 変更 {i}: Key={row_info['key']}")
            lines.append("")
            lines.append("**ID列:**")
            lines.append("```json")
            lines.append(json.dumps(row_info['id_columns'], ensure_ascii=False, indent=2))
            lines.append("```")
            lines.append("")
            lines.append("**差分:**")
            for col, values in row_info['differences'].items():
                lines.append(f"- **{col}**:")
                lines.append(f"  - 生成結果: `{values['generated']}`")
                lines.append(f"  - 正解: `{values['correct']}`")
            lines.append("")
        if len(diff_result['modified_rows']) > 10:
            lines.append(f"*（他 {len(diff_result['modified_rows']) - 10} 件）*")
            lines.append("")

    return '\n'.join(lines)

def main():
    target_file = '/Users/junki.mizutani/Documents/workspace/glow/glow-brain-repos/glow-brain-hotei/domain/tasks/masterdata-entry/create-masterdata-from-biz-ops-specs/results/202601010/diffs/target_files.json'

    with open(target_file, 'r', encoding='utf-8') as f:
        target_data = json.load(f)

    file_pairs = target_data['file_pairs']
    diffs_dir = Path(target_file).parent

    print(f"比較対象ファイル数: {len(file_pairs)}")
    print("=" * 80)

    all_diff_results = []

    for i, pair in enumerate(file_pairs, 1):
        filename = pair['filename']
        generated = pair['generated']
        correct = pair['correct']

        print(f"\n[{i}/{len(file_pairs)}] {filename} を比較中...")

        diff_result = compare_csvs(generated, correct)
        all_diff_results.append(diff_result)

        report_content = create_diff_report(diff_result)
        report_filename = filename.replace('.csv', '_diff.md')
        report_path = diffs_dir / report_filename

        with open(report_path, 'w', encoding='utf-8') as f:
            f.write(report_content)

        if diff_result['has_diff']:
            print(f"  ✗ 差分あり: +{len(diff_result['added_rows'])} -{len(diff_result['deleted_rows'])} ~{len(diff_result['modified_rows'])}")
        else:
            print(f"  ✓ 差分なし")

    summary_path = diffs_dir / 'diff_summary.json'
    with open(summary_path, 'w', encoding='utf-8') as f:
        json.dump(all_diff_results, f, ensure_ascii=False, indent=2)

    print("\n" + "=" * 80)
    print(f"差分比較完了！")
    print(f"- 個別レポート: {diffs_dir}/*_diff.md")
    print(f"- サマリーJSON: {summary_path}")

if __name__ == '__main__':
    main()
