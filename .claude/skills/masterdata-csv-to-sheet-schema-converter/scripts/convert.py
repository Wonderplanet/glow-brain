#!/usr/bin/env python3
"""
マスタデータCSV → sheet_schema CSV変換スクリプト

指定ディレクトリにあるマスタデータCSVファイルを検証・修正し、
sheet_schema形式CSVに変換します。

使用方法:
    python convert.py \
        --input-dir <変換元CSVディレクトリ> \
        [--output-dir <出力先ディレクトリ>] \
        [--masterdata-ref-dir projects/glow-masterdata] \
        [--schema-ref-dir projects/glow-masterdata/sheet_schema] \
        [--dry-run]
"""

import sys
import argparse
import csv
import json
from pathlib import Path


def read_csv_rows(path: Path) -> list[list[str]]:
    """CSVファイルを全行読み込む。"""
    with open(path, encoding='utf-8-sig', newline='') as f:
        return list(csv.reader(f))


def write_csv_rows(path: Path, rows: list[list[str]]) -> None:
    """CSVファイルを書き込む（LF改行、UTF-8）。"""
    with open(path, 'w', encoding='utf-8', newline='') as f:
        writer = csv.writer(f)
        writer.writerows(rows)


def validate_and_fix_masterdata_header(
    input_path: Path,
    ref_path: Path,
    dry_run: bool
) -> dict:
    """
    マスタデータCSVのヘッダー行（行1）を参照CSVと照合し、必要なら修正する。

    Returns:
        dict: {valid, issues, fixed}
    """
    issues = []

    input_rows = read_csv_rows(input_path)
    ref_rows = read_csv_rows(ref_path)

    if not input_rows:
        return {"valid": False, "issues": ["入力CSVが空です"], "fixed": False}
    if not ref_rows:
        return {"valid": False, "issues": ["参照CSVが空です"], "fixed": False}

    input_header = [c.strip() for c in input_rows[0]]
    ref_header = [c.strip() for c in ref_rows[0]]

    # 列名の集合比較
    input_cols_set = set(input_header)
    ref_cols_set = set(ref_header)

    extra_cols = input_cols_set - ref_cols_set
    missing_cols = ref_cols_set - input_cols_set

    if extra_cols:
        issues.append(f"入力CSVに余分な列があります（除外します）: {sorted(extra_cols)}")
    if missing_cols:
        issues.append(f"入力CSVに列が欠損しています（空列で補完します）: {sorted(missing_cols)}")
    if input_header != ref_header:
        issues.append(f"列順不一致: 参照='{','.join(ref_header)}' 入力='{','.join(input_header)}'")

    valid = not issues
    fixed = False

    if not valid:
        # 修正: 参照の列順に並び替え・欠損補完・余分列除外
        # データ行の列を辞書化して参照列順に並び替える
        fixed_rows = []
        fixed_header = ref_header[:]

        for row in input_rows[1:]:
            # 入力データを列名→値の辞書に変換
            row_dict = {}
            for i, col in enumerate(input_header):
                if col in ref_cols_set:  # 余分な列は無視
                    row_dict[col] = row[i] if i < len(row) else ''
            # 参照列順で出力（欠損列は空欄）
            fixed_row = [row_dict.get(col, '') for col in ref_header]
            fixed_rows.append(fixed_row)

        if not dry_run:
            write_csv_rows(input_path, [fixed_header] + fixed_rows)
            fixed = True
        else:
            fixed = False  # dry-runなので実際には修正しない

    return {"valid": valid, "issues": issues, "fixed": fixed}


def get_schema_header_rows(schema_path: Path) -> list[list[str]]:
    """
    sheet_schema CSVの行1-3（ヘッダー部分）を取得する。

    Returns:
        list[list[str]]: [行1, 行2, 行3]
    """
    rows = read_csv_rows(schema_path)
    if len(rows) < 3:
        return []
    return rows[:3]


def generate_sheet_schema_csv(
    input_path: Path,
    schema_path: Path,
    output_path: Path,
    dry_run: bool
) -> dict:
    """
    マスタデータCSVからsheet_schema CSV（3行ヘッダー + データ行）を生成する。

    Returns:
        dict: {output_path, validation: {valid, issues}}
    """
    # 参照sheet_schemaのヘッダー行を取得
    schema_header_rows = get_schema_header_rows(schema_path)
    if not schema_header_rows:
        return {
            "output_path": None,
            "validation": {"valid": False, "issues": ["参照sheet_schemaの行数が3行未満です"]}
        }

    # 行3（index=2）: ENABLE行からカラム定義を取得
    enable_row = [c.strip() for c in schema_header_rows[2]]
    # sheet_schemaの全カラム（ENABLE含む）
    schema_all_cols = enable_row  # ['ENABLE', 'id', 'col1', ..., 'xxx.ja', ...]

    # 入力マスタデータCSVを読み込む
    input_rows = read_csv_rows(input_path)
    if not input_rows:
        return {
            "output_path": None,
            "validation": {"valid": False, "issues": ["入力CSVが空です"]}
        }

    input_header = [c.strip() for c in input_rows[0]]  # ['ENABLE', 'id', 'col1', ...]
    data_rows = input_rows[1:]

    # 入力データを辞書化（ENABLE列を含む）
    # 入力のENABLE列（行1先頭）→ sheet_schemaのENABLE列へマッピング
    # i18n列（xxx.ja）は空欄

    output_rows = []

    for data_row in data_rows:
        # 入力データを列名→値の辞書に変換
        row_dict = {}
        for i, col in enumerate(input_header):
            row_dict[col] = data_row[i] if i < len(data_row) else ''

        # sheet_schemaの列順でデータを出力
        # i18n列（.ja サフィックス）はrow_dictに存在しないので空欄になる
        output_row = []
        for col in schema_all_cols:
            output_row.append(row_dict.get(col, ''))
        output_rows.append(output_row)

    # sheet_schema CSV = 行1-3（参照からコピー）+ データ行
    final_rows = schema_header_rows + output_rows

    if not dry_run:
        output_path.parent.mkdir(parents=True, exist_ok=True)
        write_csv_rows(output_path, final_rows)

    # サニティチェック: 生成CSVの行1-3が参照と一致するか確認
    validation_issues = []
    if not dry_run and output_path.exists():
        generated_rows = read_csv_rows(output_path)
        if len(generated_rows) < 3:
            validation_issues.append("生成CSVのヘッダー行数が3行未満です")
        else:
            for i in range(3):
                gen_row = [c.strip() for c in generated_rows[i]]
                ref_row = [c.strip() for c in schema_header_rows[i]]
                if gen_row != ref_row:
                    validation_issues.append(
                        f"行{i + 1}が参照と不一致: 参照='{','.join(ref_row[:5])}...' "
                        f"生成='{','.join(gen_row[:5])}...'"
                    )

    return {
        "output_path": str(output_path) if not dry_run else None,
        "validation": {
            "valid": len(validation_issues) == 0,
            "issues": validation_issues
        }
    }


def main():
    parser = argparse.ArgumentParser(
        description='マスタデータCSVをsheet_schema形式CSVに変換'
    )
    parser.add_argument(
        '--input-dir',
        required=True,
        help='変換元CSVが置かれたディレクトリ'
    )
    parser.add_argument(
        '--output-dir',
        default=None,
        help='sheet_schema CSV出力先ディレクトリ（デフォルト: {input-dir}/sheet_schema）'
    )
    parser.add_argument(
        '--masterdata-ref-dir',
        default='projects/glow-masterdata',
        help='マスタデータCSV参照元ディレクトリ（デフォルト: projects/glow-masterdata）'
    )
    parser.add_argument(
        '--schema-ref-dir',
        default='projects/glow-masterdata/sheet_schema',
        help='sheet_schema CSV参照元ディレクトリ（デフォルト: projects/glow-masterdata/sheet_schema）'
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='修正内容を表示するがファイルは書き込まない'
    )

    args = parser.parse_args()

    input_dir = Path(args.input_dir)
    output_dir = Path(args.output_dir) if args.output_dir else input_dir / 'sheet_schema'
    masterdata_ref_dir = Path(args.masterdata_ref_dir)
    schema_ref_dir = Path(args.schema_ref_dir)

    # 入力ディレクトリの存在確認
    if not input_dir.exists():
        print(json.dumps({
            "error": f"入力ディレクトリが見つかりません: {input_dir}"
        }, ensure_ascii=False))
        sys.exit(1)

    # 参照ディレクトリの存在確認
    if not masterdata_ref_dir.exists():
        print(json.dumps({
            "error": f"マスタデータ参照ディレクトリが見つかりません: {masterdata_ref_dir}"
        }, ensure_ascii=False))
        sys.exit(1)

    if not schema_ref_dir.exists():
        print(json.dumps({
            "error": f"sheet_schema参照ディレクトリが見つかりません: {schema_ref_dir}"
        }, ensure_ascii=False))
        sys.exit(1)

    # 入力CSVファイルを取得（ソート済み）
    csv_files = sorted(input_dir.glob('*.csv'))

    if not csv_files:
        print(json.dumps({
            "warning": f"CSVファイルが見つかりません: {input_dir}",
            "results": [],
            "summary": {
                "total": 0,
                "masterdata_issues_found": 0,
                "masterdata_fixed": 0,
                "sheet_schema_generated": 0,
                "sheet_schema_valid": 0,
                "skipped": 0
            }
        }, ensure_ascii=False, indent=2))
        sys.exit(0)

    results = []
    total_issues_found = 0
    total_fixed = 0
    total_generated = 0
    total_valid = 0
    total_skipped = 0

    for csv_file in csv_files:
        table_name = csv_file.stem  # e.g. "MstAbility"

        # 参照マスタデータCSVの確認
        ref_masterdata = masterdata_ref_dir / csv_file.name
        if not ref_masterdata.exists():
            results.append({
                "table": table_name,
                "skipped": True,
                "skip_reason": f"参照マスタデータCSVが見つかりません: {ref_masterdata}"
            })
            total_skipped += 1
            continue

        # 参照sheet_schema CSVの確認
        ref_schema = schema_ref_dir / csv_file.name
        if not ref_schema.exists():
            results.append({
                "table": table_name,
                "skipped": True,
                "skip_reason": f"参照sheet_schema CSVが見つかりません: {ref_schema}"
            })
            total_skipped += 1
            continue

        # ① マスタデータCSVヘッダー検証・修正
        masterdata_result = validate_and_fix_masterdata_header(
            input_path=csv_file,
            ref_path=ref_masterdata,
            dry_run=args.dry_run
        )

        if not masterdata_result["valid"]:
            total_issues_found += 1
        if masterdata_result["fixed"]:
            total_fixed += 1

        # ② sheet_schema CSV生成
        output_path = output_dir / csv_file.name
        schema_result = generate_sheet_schema_csv(
            input_path=csv_file,
            schema_path=ref_schema,
            output_path=output_path,
            dry_run=args.dry_run
        )

        generated_path = schema_result["output_path"]
        if generated_path:
            total_generated += 1
        elif not args.dry_run:
            # dry-run以外で生成失敗
            pass
        else:
            # dry-runの場合は生成予定としてカウント
            total_generated += 1

        if schema_result["validation"]["valid"]:
            total_valid += 1

        results.append({
            "table": table_name,
            "masterdata_validation": masterdata_result,
            "sheet_schema_output": generated_path or (str(output_path) if args.dry_run else None),
            "sheet_schema_validation": schema_result["validation"]
        })

    output = {
        "results": results,
        "summary": {
            "total": len(csv_files),
            "masterdata_issues_found": total_issues_found,
            "masterdata_fixed": total_fixed,
            "sheet_schema_generated": total_generated,
            "sheet_schema_valid": total_valid,
            "skipped": total_skipped
        }
    }

    print(json.dumps(output, ensure_ascii=False, indent=2))
    sys.exit(0)


if __name__ == "__main__":
    main()
