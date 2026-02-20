#!/usr/bin/env python3
"""
統合検証スクリプト

全ての検証を実行して統合レポートを生成します。

使用方法:
    # sheet_schemaモード（デフォルト）: sheet_schemaテンプレートとの比較
    python validate_all.py --csv <CSVファイルパス>

    # masterdataモード: 既存の実マスタデータCSVとの比較・自動修正
    python validate_all.py --csv <CSVファイルパス> --mode masterdata

    # masterdataモードで参照CSVを明示指定
    python validate_all.py \
        --csv <CSVファイルパス> \
        --mode masterdata \
        --reference-csv projects/glow-masterdata/MstAbility.csv

    # masterdataモードでdry-run（修正内容のみ確認）
    python validate_all.py --csv <CSVファイルパス> --mode masterdata --dry-run
"""

import sys
import argparse
import json
import subprocess
from pathlib import Path
from typing import Dict, List, Any, Optional


def run_validation_script(script_path: str, args: List[str]) -> Dict[str, Any]:
    """
    検証スクリプトを実行して結果を取得

    Args:
        script_path: スクリプトのパス
        args: スクリプトへの引数リスト

    Returns:
        result: 検証結果（JSON）
    """
    try:
        result = subprocess.run(
            ['python3', script_path] + args,
            capture_output=True,
            text=True,
            check=False
        )

        if result.stdout:
            return json.loads(result.stdout)
        else:
            return {
                "valid": False,
                "error": f"スクリプト実行エラー: {result.stderr}"
            }

    except json.JSONDecodeError as e:
        return {
            "valid": False,
            "error": f"JSON解析エラー: {str(e)}"
        }
    except Exception as e:
        return {
            "valid": False,
            "error": f"予期しないエラー: {str(e)}"
        }


def validate_all_sheet_schema(csv_path: str) -> Dict[str, Any]:
    """
    sheet_schemaモードの統合検証（既存の動作）

    sheet_schema/*.csv（スキーマ定義テンプレート）との比較検証を実行します。

    Args:
        csv_path: CSVファイルのパス

    Returns:
        result: 統合検証結果
    """
    if not Path(csv_path).exists():
        return {
            "file": Path(csv_path).name,
            "valid": False,
            "error": f"CSVファイルが見つかりません: {csv_path}"
        }

    # スクリプトディレクトリ
    script_dir = Path(__file__).parent

    # テンプレートパスの推測
    csv_filename = Path(csv_path).name
    template_path = f"projects/glow-masterdata/sheet_schema/{csv_filename}"

    # DBスキーマパス
    schema_path = "projects/glow-server/api/database/schema/exports/master_tables_schema.json"

    results = {
        "file": csv_filename,
        "mode": "sheet_schema",
        "validations": {},
        "summary": {
            "total_issues": 0,
            "critical_issues": 0,
            "warnings": 0
        }
    }

    # 1. テンプレート検証
    if Path(template_path).exists():
        print(f"🔍 テンプレート検証中...", file=sys.stderr)
        template_result = run_validation_script(
            str(script_dir / 'validate_template.py'),
            ['--generated', csv_path, '--template', template_path]
        )
        results['validations']['template'] = template_result
    else:
        results['validations']['template'] = {
            "valid": False,
            "warning": f"テンプレートファイルが見つかりません: {template_path}",
            "skipped": True
        }

    # 2. CSV形式検証
    print(f"🔍 CSV形式検証中...", file=sys.stderr)
    format_result = run_validation_script(
        str(script_dir / 'validate_csv_format.py'),
        [csv_path]
    )
    results['validations']['format'] = format_result

    # 3. DBスキーマ検証
    if Path(schema_path).exists():
        print(f"🔍 DBスキーマ検証中...", file=sys.stderr)
        schema_result = run_validation_script(
            str(script_dir / 'validate_schema.py'),
            ['--csv', csv_path, '--schema', schema_path]
        )
        results['validations']['schema'] = schema_result
    else:
        results['validations']['schema'] = {
            "valid": False,
            "warning": f"スキーマファイルが見つかりません: {schema_path}",
            "skipped": True
        }

    # 4. Enum値検証
    print(f"🔍 Enum値検証中...", file=sys.stderr)
    enum_result = run_validation_script(
        str(script_dir / 'validate_enum.py'),
        ['--csv', csv_path]
    )
    results['validations']['enum'] = enum_result

    # サマリー集計
    for validation_name, validation_result in results['validations'].items():
        if validation_result.get('skipped'):
            continue

        issues = validation_result.get('issues', [])
        results['summary']['total_issues'] += len(issues)

        for issue in issues:
            if issue.get('severity') == 'warning':
                results['summary']['warnings'] += 1
            else:
                results['summary']['critical_issues'] += 1

    # 全体の valid 判定
    all_valid = all(
        v.get('valid', False) or v.get('skipped', False)
        for v in results['validations'].values()
    )
    results['valid'] = all_valid and results['summary']['critical_issues'] == 0

    return results


def validate_all_masterdata(
    csv_path: str,
    reference_csv: Optional[str] = None,
    dry_run: bool = False
) -> Dict[str, Any]:
    """
    masterdataモードの統合検証

    既存の実マスタデータCSVとの比較・自動修正を実行します。

    Args:
        csv_path: 検証対象CSVファイルのパス
        reference_csv: 参照CSVのパス（省略時は自動推測）
        dry_run: Trueの場合は修正内容のみ出力（CSVは書き換えない）

    Returns:
        result: 統合検証結果
    """
    if not Path(csv_path).exists():
        return {
            "file": Path(csv_path).name,
            "mode": "masterdata",
            "valid": False,
            "error": f"CSVファイルが見つかりません: {csv_path}"
        }

    # スクリプトディレクトリ
    script_dir = Path(__file__).parent

    print(f"🔍 masterdataモード検証中...", file=sys.stderr)

    # validate_masterdata.py に渡す引数を構築
    masterdata_args = ['--csv', csv_path]
    if reference_csv:
        masterdata_args.extend(['--reference-csv', reference_csv])
    if dry_run:
        masterdata_args.append('--dry-run')

    masterdata_result = run_validation_script(
        str(script_dir / 'validate_masterdata.py'),
        masterdata_args
    )

    return masterdata_result


def validate_all(
    csv_path: str,
    mode: str = 'sheet_schema',
    reference_csv: Optional[str] = None,
    dry_run: bool = False
) -> Dict[str, Any]:
    """
    統合検証を実行（モードに応じて処理分岐）

    Args:
        csv_path: CSVファイルのパス
        mode: 'sheet_schema' または 'masterdata'
        reference_csv: 参照CSVのパス（masterdataモード時に使用）
        dry_run: masterdataモード時のdry-runフラグ

    Returns:
        result: 統合検証結果
    """
    if mode == 'masterdata':
        return validate_all_masterdata(csv_path, reference_csv, dry_run)
    else:
        return validate_all_sheet_schema(csv_path)


def main():
    parser = argparse.ArgumentParser(
        description='マスタデータCSVの統合検証'
    )
    parser.add_argument(
        '--csv',
        required=True,
        help='CSVファイルのパス'
    )
    parser.add_argument(
        '--mode',
        choices=['sheet_schema', 'masterdata'],
        default='sheet_schema',
        help='検証モード: sheet_schema（デフォルト）または masterdata'
    )
    parser.add_argument(
        '--reference-csv',
        help='参照CSVのパス（masterdataモード時。省略時はファイル名から自動推測）'
    )
    parser.add_argument(
        '--dry-run',
        action='store_true',
        help='masterdataモード: 修正内容をレポートのみ出力してCSVは書き換えない'
    )

    args = parser.parse_args()

    # 検証実行
    result = validate_all(
        args.csv,
        mode=args.mode,
        reference_csv=args.reference_csv,
        dry_run=args.dry_run
    )

    # JSON出力
    print(json.dumps(result, ensure_ascii=False, indent=2))

    # 終了コード
    sys.exit(0 if result.get("valid", False) else 1)


if __name__ == "__main__":
    main()
