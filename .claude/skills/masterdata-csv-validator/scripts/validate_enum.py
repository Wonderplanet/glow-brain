#!/usr/bin/env python3
"""
Enum値検証スクリプト

CSVデータのEnum列の値が許可された範囲内かを検証します。

使用方法:
    python validate_enum.py --csv <CSVファイルパス>
"""

import sys
import argparse
import csv
import json
import subprocess
import re
from pathlib import Path
from typing import Dict, List, Any, Set, Optional


def get_table_name_from_csv(csv_filename: str) -> str:
    """
    CSVファイル名からテーブル名を推測

    例: MstUnit.csv -> MstUnit
        OprGacha.csv -> OprGacha
    """
    return Path(csv_filename).stem


def read_csv_data(csv_path: str) -> tuple:
    """CSVファイルを読み取ってカラム名とデータ行を返す"""
    with open(csv_path, 'r', encoding='utf-8-sig') as f:
        reader = csv.reader(f)
        rows = list(reader)

    if len(rows) < 4:
        raise ValueError("CSVファイルには最低4行（ヘッダー3行+データ1行以上）必要です")

    # ヘッダー行からカラム名を抽出（3行目）
    header_row = rows[2]
    if header_row[0] == 'ENABLE':
        columns = header_row[1:]  # ENABLEを除く
    else:
        columns = header_row

    # データ行（4行目以降、'd' または 'e' 行のみ）
    data_rows = []
    for row_num, row in enumerate(rows[3:], start=4):
        if row and row[0] in ['d', 'e']:
            data_rows.append((row_num, row[1:]))  # 行番号と値（d/e除外）

    return columns, data_rows


def get_enum_columns(data_name: str) -> Dict[str, Dict[str, Any]]:
    """
    enum_detector.shを使用してEnum列を検出

    Returns:
        {
            'roleType': {
                'csharp_enum': 'CharacterUnitRoleType',
                'php_enum': 'RoleType'
            },
            ...
        }
    """
    try:
        # 同じディレクトリのenum_detector.shを使用
        script_dir = Path(__file__).parent
        enum_detector = str(script_dir / 'enum_detector.sh')

        result = subprocess.run(
            [enum_detector, 'detect', data_name],
            capture_output=True,
            text=True,
            check=False
        )

        if result.returncode != 0:
            return {}

        # 出力をパース
        # 例: "  roleType -> CharacterUnitRoleType(C#), RoleType(PHP)"
        enum_columns = {}
        for line in result.stdout.strip().split('\n'):
            line = line.strip()
            if '->' not in line:
                continue

            parts = line.split('->')
            if len(parts) != 2:
                continue

            column_name = parts[0].strip()
            enum_info = parts[1].strip()

            # Enum名を抽出
            # "CharacterUnitRoleType(C#), RoleType(PHP)" または "UnitLabel(C#/PHP)"
            csharp_enum = None
            php_enum = None

            if '(C#/PHP)' in enum_info:
                # 同名の場合
                enum_name = enum_info.split('(')[0].strip()
                csharp_enum = enum_name
                php_enum = enum_name
            else:
                # 異名の場合
                for part in enum_info.split(','):
                    part = part.strip()
                    if '(C#)' in part:
                        csharp_enum = part.split('(')[0].strip()
                    elif '(PHP)' in part:
                        php_enum = part.split('(')[0].strip()

            enum_columns[column_name] = {
                'csharp_enum': csharp_enum,
                'php_enum': php_enum
            }

        return enum_columns

    except Exception as e:
        print(f"Warning: Enum検出エラー: {e}", file=sys.stderr)
        return {}


def get_enum_values(enum_name: str, lang: str) -> Set[str]:
    """
    enum_detector.shを使用してEnum値を取得

    Args:
        enum_name: Enum名
        lang: 'php' または 'csharp'

    Returns:
        Enum値のセット
    """
    try:
        command = f'{lang}-enum' if lang in ['php', 'csharp'] else None
        if not command:
            return set()

        # 同じディレクトリのenum_detector.shを使用
        script_dir = Path(__file__).parent
        enum_detector = str(script_dir / 'enum_detector.sh')

        result = subprocess.run(
            [enum_detector, command, enum_name],
            capture_output=True,
            text=True,
            check=False
        )

        if result.returncode != 0 or not result.stdout:
            return set()

        # 1行1値で返される
        values = set(result.stdout.strip().split('\n'))
        return {v for v in values if v}  # 空行を除外

    except Exception as e:
        print(f"Warning: Enum値取得エラー ({enum_name}): {e}", file=sys.stderr)
        return set()


def validate_enum(csv_path: str) -> Dict[str, Any]:
    """
    Enum値検証を実行

    Returns:
        {
            "valid": bool,
            "issues": [...]
        }
    """
    csv_filename = Path(csv_path).name
    data_name = get_table_name_from_csv(csv_filename)

    issues = []

    try:
        # CSVデータを読み取る
        columns, data_rows = read_csv_data(csv_path)

        # Enum列を検出
        enum_columns = get_enum_columns(data_name)

        if not enum_columns:
            # Enum列が見つからない場合はスキップ（エラーではない）
            return {
                "valid": True,
                "issues": [],
                "info": "Enum列が検出されませんでした（スキップ）"
            }

        # カラム名からインデックスのマッピングを作成
        column_indices = {col: idx for idx, col in enumerate(columns)}

        # 各Enum列を検証
        for column_name, enum_info in enum_columns.items():
            if column_name not in column_indices:
                continue

            col_idx = column_indices[column_name]

            # Enum値を取得（C#とPHPの両方）
            allowed_values = set()

            if enum_info.get('csharp_enum'):
                csharp_values = get_enum_values(enum_info['csharp_enum'], 'csharp')
                allowed_values.update(csharp_values)

            if enum_info.get('php_enum'):
                php_values = get_enum_values(enum_info['php_enum'], 'php')
                allowed_values.update(php_values)

            if not allowed_values:
                issues.append({
                    "severity": "warning",
                    "column": column_name,
                    "message": f"Enum値を取得できませんでした（C#: {enum_info.get('csharp_enum')}, PHP: {enum_info.get('php_enum')}）"
                })
                continue

            # 各行のEnum値をチェック
            for row_num, row_data in data_rows:
                if col_idx >= len(row_data):
                    continue

                value = row_data[col_idx]

                # 空値はスキップ（NULL許可チェックは別の検証で行う）
                if not value or value == '__NULL__':
                    continue

                # Enum値範囲チェック
                if value not in allowed_values:
                    issues.append({
                        "severity": "error",
                        "row": row_num,
                        "column": column_name,
                        "value": value,
                        "message": f"許可されていないEnum値です",
                        "allowed_values": sorted(list(allowed_values)),
                        "enum_name": f"{enum_info.get('csharp_enum') or enum_info.get('php_enum')}"
                    })

    except Exception as e:
        issues.append({
            "severity": "error",
            "message": f"Enum検証エラー: {str(e)}"
        })

    return {
        "valid": len([i for i in issues if i.get('severity') == 'error']) == 0,
        "issues": issues
    }


def main():
    parser = argparse.ArgumentParser(
        description='マスタデータCSVのEnum値検証'
    )
    parser.add_argument(
        '--csv',
        required=True,
        help='CSVファイルのパス'
    )

    args = parser.parse_args()

    # 検証実行
    result = validate_enum(args.csv)

    # JSON出力
    print(json.dumps(result, ensure_ascii=False, indent=2))

    # 終了コード
    sys.exit(0 if result.get("valid", False) else 1)


if __name__ == "__main__":
    main()
