#!/usr/bin/env python3
"""
masterdataモード専用の比較・自動修正スクリプト

既存の実マスタデータCSVと検証対象CSVを比較し、
列ヘッダーや列位置の不一致を検出・自動修正します。

使用方法:
    # 自動修正あり（デフォルト）
    python validate_masterdata.py --csv path/to/MstAbility.csv

    # dry-runで修正内容だけ確認
    python validate_masterdata.py --csv path/to/MstAbility.csv --dry-run

    # 参照CSVを明示指定
    python validate_masterdata.py \
        --csv path/to/MstAbility.csv \
        --reference-csv projects/glow-masterdata/MstAbility.csv

    # 自動修正を無効にして検証のみ
    python validate_masterdata.py --csv path/to/MstAbility.csv --no-fix
"""

import sys
import argparse
import csv
import json
from pathlib import Path
from typing import Dict, List, Any, Optional, Tuple


def infer_reference_csv(csv_path: str) -> str:
    """
    CSVファイル名から参照CSVパスを推測する

    例:
        /path/to/MstAbility.csv -> projects/glow-masterdata/MstAbility.csv
        /any/path/OprGacha.csv  -> projects/glow-masterdata/OprGacha.csv
    """
    filename = Path(csv_path).name
    return f"projects/glow-masterdata/{filename}"


def read_csv_data(csv_path: str) -> Tuple[List[str], List[Dict[str, str]]]:
    """
    CSVを読み込んでヘッダーと全データを返す

    Returns:
        (headers, rows): ヘッダーリストと辞書のリスト
    """
    with open(csv_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        headers = list(reader.fieldnames or [])
        rows = list(reader)
    return headers, rows


def check_csv_header_format(headers: List[str], label: str) -> List[Dict[str, Any]]:
    """
    ヘッダー行が ENABLE,col1,col2,... 形式か確認（段階1）

    Args:
        headers: CSVのヘッダーリスト
        label: エラーメッセージ用のファイル識別子
    """
    issues = []
    if not headers:
        issues.append({
            "type": "invalid_header",
            "severity": "error",
            "message": f"ヘッダーが空です ({label})"
        })
    elif headers[0] != 'ENABLE':
        issues.append({
            "type": "invalid_header",
            "severity": "warning",
            "message": f"1列目が 'ENABLE' ではありません: '{headers[0]}' ({label})"
        })
    return issues


def compare_columns(
    target_headers: List[str],
    ref_headers: List[str]
) -> List[Dict[str, Any]]:
    """
    カラム名・順序の比較（段階2）

    - 欠損カラム（refにあってtargetにない） → エラー
    - 余分なカラム（targetにあってrefにない） → 警告
    - カラム順序の不一致 → エラー（自動修正対象）
    """
    issues = []

    # ENABLE を除いたカラムリスト
    target_cols = target_headers[1:] if target_headers and target_headers[0] == 'ENABLE' else target_headers
    ref_cols = ref_headers[1:] if ref_headers and ref_headers[0] == 'ENABLE' else ref_headers

    target_set = set(target_cols)
    ref_set = set(ref_cols)

    # 欠損カラム（refにあってtargetにない）
    missing = ref_set - target_set
    for col in sorted(missing):
        issues.append({
            "type": "missing_column",
            "severity": "error",
            "column": col,
            "message": f"参照CSVに存在するカラムがありません: '{col}'"
        })

    # 余分なカラム（targetにあってrefにない）
    extra = target_set - ref_set
    for col in sorted(extra):
        issues.append({
            "type": "extra_column",
            "severity": "warning",
            "column": col,
            "message": f"参照CSVに存在しないカラムがあります: '{col}'"
        })

    # カラム順序の不一致（共通カラムで比較）
    common_in_target = [c for c in target_cols if c in ref_set]
    common_in_ref = [c for c in ref_cols if c in target_set]

    if common_in_target != common_in_ref:
        issues.append({
            "type": "column_order_mismatch",
            "severity": "error",
            "expected": ref_headers,
            "actual": target_headers,
            "message": "カラムの順序が参照CSVと異なります（自動修正対象）"
        })

    return issues


def build_inspection_data(
    target_headers: List[str],
    target_rows: List[Dict[str, str]],
    ref_headers: List[str],
    ref_rows: List[Dict[str, str]],
    max_samples: int = 5
) -> Dict[str, Any]:
    """
    Claudeによる目視確認用のサンプルデータを生成（段階3）

    スクリプトではなくClaudeが判別するため、この段階では
    判断材料となるデータを出力するのみ。

    出力内容:
    1. 参照CSVと検証対象CSVのカラム名リスト
    2. 共通IDのレコードをサンプリング（最大5件）
    3. 各カラムの代表的な値の例（参照CSVから）
    """
    # IDカラムを特定（"id" があれば使う）
    id_col = None
    for col in target_headers:
        if col.lower() == 'id':
            id_col = col
            break

    # 共通IDのレコードをサンプリング
    sample_records = []
    if id_col and ref_rows:
        ref_by_id = {row.get(id_col): row for row in ref_rows if row.get(id_col)}
        count = 0
        for row in target_rows:
            if count >= max_samples:
                break
            row_id = row.get(id_col)
            if row_id and row_id in ref_by_id:
                sample_records.append({
                    "id": row_id,
                    "target": dict(row),
                    "reference": dict(ref_by_id[row_id])
                })
                count += 1
    elif target_rows:
        for row in target_rows[:max_samples]:
            sample_records.append({
                "target": dict(row),
                "reference": None
            })

    # 各カラムの代表的な値例（参照CSVから、最大3件）
    ref_col_examples: Dict[str, List[str]] = {}
    if ref_rows:
        for col in ref_headers:
            if col == 'ENABLE':
                continue
            values = [
                row.get(col, '')
                for row in ref_rows[:10]
                if row.get(col)
            ]
            ref_col_examples[col] = values[:3]

    return {
        "column_comparison": {
            "target_columns": target_headers,
            "reference_columns": ref_headers
        },
        "sample_records": sample_records,
        "reference_column_examples": ref_col_examples
    }


def apply_column_reorder(
    csv_path: str,
    target_headers: List[str],
    target_rows: List[Dict[str, str]],
    ref_headers: List[str],
    dry_run: bool = False
) -> Dict[str, Any]:
    """
    カラム順序を参照CSVに合わせて修正（段階4）

    Args:
        csv_path: 修正対象のCSVパス
        target_headers: 対象CSVのヘッダー
        target_rows: 対象CSVのデータ行
        ref_headers: 参照CSVのヘッダー（目標の順序）
        dry_run: Trueの場合はCSVを書き換えずに修正内容のみ出力

    Returns:
        fix_result: 修正内容
    """
    target_set = set(target_headers)

    # 参照CSVの順序で並べる（targetに存在するカラムのみ）
    new_column_order = [col for col in ref_headers if col in target_set]

    # targetにしかないカラムは末尾に追加
    extra_cols = [col for col in target_headers if col not in set(ref_headers)]
    new_column_order.extend(extra_cols)

    if not dry_run:
        # CSVを新しいカラム順序で書き直す（バックアップなし: Gitで管理）
        with open(csv_path, 'w', encoding='utf-8', newline='') as f:
            writer = csv.DictWriter(
                f,
                fieldnames=new_column_order,
                extrasaction='ignore'
            )
            writer.writeheader()
            for row in target_rows:
                writer.writerow(row)

    return {
        "action": "reorder_columns",
        "column_order": new_column_order,
        "applied": not dry_run
    }


def validate_masterdata_csv(
    csv_path: str,
    reference_csv_path: Optional[str] = None,
    fix: bool = True,
    dry_run: bool = False
) -> Dict[str, Any]:
    """
    masterdataモードの検証・修正メイン処理

    Args:
        csv_path: 検証対象CSVのパス
        reference_csv_path: 参照CSVのパス（省略時は自動推測）
        fix: Trueの場合に自動修正を実行
        dry_run: Trueの場合は修正内容のみ出力（CSVは書き換えない）

    Returns:
        result: 検証結果（JSON形式）
    """
    # 参照CSVパスの自動推測
    if not reference_csv_path:
        reference_csv_path = infer_reference_csv(csv_path)

    result: Dict[str, Any] = {
        "file": Path(csv_path).name,
        "mode": "masterdata",
        "reference": reference_csv_path,
        "valid": False,
        "fixed": False,
        "issues": [],
        "inspection_data": None,
        "applied_fix": None
    }

    # ファイル存在確認
    if not Path(csv_path).exists():
        result["issues"].append({
            "type": "file_not_found",
            "severity": "error",
            "message": f"CSVファイルが見つかりません: {csv_path}"
        })
        return result

    if not Path(reference_csv_path).exists():
        result["issues"].append({
            "type": "reference_not_found",
            "severity": "error",
            "message": f"参照CSVファイルが見つかりません: {reference_csv_path}"
        })
        return result

    # CSVデータ読み込み
    try:
        target_headers, target_rows = read_csv_data(csv_path)
        ref_headers, ref_rows = read_csv_data(reference_csv_path)
    except Exception as e:
        result["issues"].append({
            "type": "read_error",
            "severity": "error",
            "message": f"CSV読み取りエラー: {str(e)}"
        })
        return result

    # 段階1: ヘッダー形式確認
    result["issues"].extend(
        check_csv_header_format(target_headers, f"検証対象: {Path(csv_path).name}")
    )
    result["issues"].extend(
        check_csv_header_format(ref_headers, f"参照: {Path(reference_csv_path).name}")
    )

    # 段階2: カラム名・順序の比較
    column_issues = compare_columns(target_headers, ref_headers)
    result["issues"].extend(column_issues)

    # 段階3: Claudeによる目視確認用データを出力
    result["inspection_data"] = build_inspection_data(
        target_headers, target_rows,
        ref_headers, ref_rows
    )

    # カラム順序不一致の有無を確認
    has_column_order_mismatch = any(
        i["type"] == "column_order_mismatch" for i in result["issues"]
    )

    # 段階4: 自動修正（Claudeの判断後に実行）
    if has_column_order_mismatch and (fix or dry_run):
        fix_result = apply_column_reorder(
            csv_path, target_headers, target_rows, ref_headers,
            dry_run=dry_run
        )
        result["applied_fix"] = fix_result

        if not dry_run:
            result["fixed"] = True
            # 修正済みの column_order_mismatch を issues から除去
            result["issues"] = [
                i for i in result["issues"]
                if i["type"] != "column_order_mismatch"
            ]

    # valid 判定
    remaining_errors = [
        i for i in result["issues"]
        if i.get("severity") == "error"
    ]
    result["valid"] = len(remaining_errors) == 0

    return result


def main():
    parser = argparse.ArgumentParser(
        description='masterdataモードのCSV検証・自動修正'
    )
    parser.add_argument(
        '--csv',
        required=True,
        help='検証対象CSVファイルのパス'
    )
    parser.add_argument(
        '--reference-csv',
        help='参照CSVファイルのパス（省略時はファイル名から自動推測）'
    )
    parser.add_argument(
        '--fix',
        action='store_true',
        default=True,
        help='問題が見つかった場合に自動修正する（デフォルト: true）'
    )
    parser.add_argument(
        '--no-fix',
        dest='fix',
        action='store_false',
        help='自動修正を無効にする'
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='修正内容をレポートのみ出力してCSVは書き換えない'
    )

    args = parser.parse_args()

    # dry-run時は fix を無効化（書き換えなし）
    fix = args.fix and not args.dry_run

    result = validate_masterdata_csv(
        args.csv,
        args.reference_csv,
        fix=fix,
        dry_run=args.dry_run
    )

    print(json.dumps(result, ensure_ascii=False, indent=2))
    sys.exit(0 if result.get('valid', False) else 1)


if __name__ == "__main__":
    main()
