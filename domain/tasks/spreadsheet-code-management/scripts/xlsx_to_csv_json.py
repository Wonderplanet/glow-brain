#!/usr/bin/env python3
"""
XLSX → CSV / JSON 変換スクリプト

Google Spreadsheet を XLSX でダウンロードし、コード管理できる形式に変換する。

出力:
  <output_dir>/
    csv/
      <シート名>.csv    # 実データ（計算済み値）
    cells.json          # 式情報（どのセルにどんな式があるか）
    metadata.json       # ブック全体のメタ情報

Usage:
    python3 scripts/xlsx_to_csv_json.py <xlsx_file>
    python3 scripts/xlsx_to_csv_json.py <xlsx_file> --output-dir outputs/my_sheet
"""

import re
import sys
import csv
import json
import argparse
from pathlib import Path
from datetime import datetime
import openpyxl
from openpyxl.utils import get_column_letter


# ---- 定数 / 正規表現 -------------------------------------------------------

# IMPORTRANGE ヘッダーパターン:
#   =IFERROR(__xludf.DUMMYFUNCTION("IMPORTRANGE(""url"",""range"")"), "")
_RE_IMPORTRANGE = re.compile(
    r'=IFERROR\(__xludf\.DUMMYFUNCTION\("IMPORTRANGE\(""(.+?)"",\s*""(.+?)""\)"\)',
    re.DOTALL,
)

# COMPUTED_VALUE パターン（IMPORTRANGE で転記されたデータセル）:
#   =IFERROR(__xludf.DUMMYFUNCTION("""COMPUTED_VALUE"""), "actual_value")
_COMPUTED_VALUE_PREFIX = '=IFERROR(__xludf.DUMMYFUNCTION("""COMPUTED_VALUE""")'


# ---- ユーティリティ ---------------------------------------------------------

def sanitize_filename(name: str) -> str:
    """シート名をファイルシステムで安全な文字列に変換"""
    for ch in r'\/:*?"<>|▶︎':
        name = name.replace(ch, '_')
    return name.strip() or 'sheet'


def cell_value_to_str(value) -> str:
    """セル値を CSV 用文字列に変換"""
    if value is None:
        return ''
    if isinstance(value, datetime):
        return value.strftime('%Y-%m-%d %H:%M:%S')
    if isinstance(value, bool):
        return 'TRUE' if value else 'FALSE'
    if isinstance(value, float):
        return str(int(value)) if value == int(value) else str(value)
    return str(value)


def extract_iferror_fallback(formula: str) -> str | None:
    """
    IFERROR(expr, fallback) の fallback 部分を抽出する。
    ネストされた括弧・文字列リテラルを考慮した簡易パーサ。
    """
    # "=IFERROR(" の後ろから深さ1の最後のカンマを探す
    depth = 0
    in_str = False
    last_comma = -1
    i = 0
    while i < len(formula):
        ch = formula[i]
        if ch == '"':
            # 連続する "" は単一のリテラル " として扱う
            if in_str and i + 1 < len(formula) and formula[i + 1] == '"':
                i += 2
                continue
            in_str = not in_str
        elif not in_str:
            if ch == '(':
                depth += 1
            elif ch == ')':
                depth -= 1
            elif ch == ',' and depth == 1:
                last_comma = i
        i += 1

    if last_comma == -1:
        return None

    # fallback 部分: カンマの後〜末尾の ')' の前
    fallback = formula[last_comma + 1:].strip()
    if fallback.endswith(')'):
        fallback = fallback[:-1].strip()

    # 前後のダブルクォートを除去
    if len(fallback) >= 2 and fallback[0] == '"' and fallback[-1] == '"':
        # "" のエスケープを戻す
        return fallback[1:-1].replace('""', '"')

    return fallback if fallback else None


def classify_formula(formula: str) -> dict:
    """
    式を分類してメタ情報を返す。

    returns: {
        "formula_type": "importrange" | "computed_value" | "normal",
        ...追加情報...
    }
    """
    if not isinstance(formula, str) or not formula.startswith('='):
        return {"formula_type": "normal", "formula": formula}

    # IMPORTRANGE ヘッダー
    m = _RE_IMPORTRANGE.match(formula)
    if m:
        return {
            "formula_type": "importrange",
            "formula": formula,
            "importrange_url": m.group(1),
            "importrange_range": m.group(2),
        }

    # COMPUTED_VALUE（IMPORTRANGE データセル）
    if formula.startswith(_COMPUTED_VALUE_PREFIX):
        fallback = extract_iferror_fallback(formula)
        return {
            "formula_type": "computed_value",
            "formula": formula,
            "value": fallback,
        }

    # 通常の式（=len(G3), =IFERROR(...) など）
    return {
        "formula_type": "normal",
        "formula": formula,
    }


# ---- CSV 出力 ---------------------------------------------------------------

def export_csv(wb_data: openpyxl.Workbook, wb_formula: openpyxl.Workbook, output_dir: Path) -> list[dict]:
    """
    全シートの実データを CSV に出力する。

    IMPORTRANGE で転記されたセル (data_only=True で None になる) は
    式の IFERROR フォールバックから値を補完する。
    """
    csv_dir = output_dir / 'csv'
    csv_dir.mkdir(exist_ok=True)

    sheet_info = []

    for sheet_name in wb_data.sheetnames:
        ws_data = wb_data[sheet_name]
        ws_form = wb_formula[sheet_name]
        safe = sanitize_filename(sheet_name)
        csv_path = csv_dir / f'{safe}.csv'

        with open(csv_path, 'w', newline='', encoding='utf-8') as f:
            writer = csv.writer(f)
            for row_data, row_form in zip(ws_data.iter_rows(), ws_form.iter_rows()):
                out_row = []
                for cell_d, cell_f in zip(row_data, row_form):
                    value = cell_d.value

                    # data_only=True で None & 式セルの場合 → フォールバックを補完
                    if value is None and cell_f.data_type == 'f' and isinstance(cell_f.value, str):
                        info = classify_formula(cell_f.value)
                        if info['formula_type'] == 'computed_value':
                            value = info.get('value')  # IMPORTRANGE 転記値
                        elif info['formula_type'] == 'importrange':
                            value = None  # ヘッダー行はそのまま空
                        # 通常式は None のまま（キャッシュなし）

                    out_row.append(cell_value_to_str(value))

                # 末尾の空列を除去してから書き込み
                while out_row and out_row[-1] == '':
                    out_row.pop()
                writer.writerow(out_row)

        sheet_info.append({
            'sheet_name': sheet_name,
            'file': f'csv/{safe}.csv',
            'max_row': ws_data.max_row,
            'max_col': ws_data.max_column,
        })

    return sheet_info


# ---- cells.json 出力 --------------------------------------------------------

def export_cells_json(wb_formula: openpyxl.Workbook, output_dir: Path) -> Path:
    """
    式を持つセルの情報を cells.json に出力する。

    出力形式:
    {
      "シート名": [
        {
          "coord": "A1",
          "row": 1,
          "col": 1,
          "col_letter": "A",
          "formula_type": "importrange" | "computed_value" | "normal",
          "formula": "=...",
          // formula_type="importrange" の場合
          "importrange_url": "https://...",
          "importrange_range": "シート!A:N",
          // formula_type="computed_value" の場合
          "value": "実際の値",
        },
        ...
      ]
    }
    """
    cells_data: dict[str, list] = {}

    for sheet_name in wb_formula.sheetnames:
        ws = wb_formula[sheet_name]
        sheet_cells = []

        for row in ws.iter_rows():
            for cell in row:
                if cell.data_type != 'f' or not isinstance(cell.value, str):
                    continue  # 式セルのみ対象

                info = classify_formula(cell.value)
                entry = {
                    'coord': cell.coordinate,
                    'row': cell.row,
                    'col': cell.column,
                    'col_letter': get_column_letter(cell.column),
                    **info,
                }
                sheet_cells.append(entry)

        if sheet_cells:
            cells_data[sheet_name] = sheet_cells

    cells_path = output_dir / 'cells.json'
    with open(cells_path, 'w', encoding='utf-8') as f:
        json.dump(cells_data, f, ensure_ascii=False, indent=2)

    return cells_path


# ---- metadata.json 出力 -----------------------------------------------------

def export_metadata(xlsx_path: Path, sheet_info: list[dict], cells_path: Path, output_dir: Path) -> Path:
    """ブック全体のメタ情報を metadata.json に出力する"""
    # シート別の式タイプ集計（cells.json から）
    with open(cells_path, encoding='utf-8') as f:
        cells_data = json.load(f)

    formula_summary = {}
    for sname, cells in cells_data.items():
        types: dict[str, int] = {}
        for c in cells:
            t = c.get('formula_type', 'normal')
            types[t] = types.get(t, 0) + 1
        formula_summary[sname] = types

    metadata = {
        'source_file': xlsx_path.name,
        'converted_at': datetime.now().isoformat(),
        'total_sheets': len(sheet_info),
        'sheets': [
            {
                **s,
                'formula_counts': formula_summary.get(s['sheet_name'], {}),
            }
            for s in sheet_info
        ],
    }

    meta_path = output_dir / 'metadata.json'
    with open(meta_path, 'w', encoding='utf-8') as f:
        json.dump(metadata, f, ensure_ascii=False, indent=2)

    return meta_path


# ---- メイン -----------------------------------------------------------------

def convert(xlsx_path: Path, output_dir: Path) -> None:
    print(f'変換開始: {xlsx_path.name}')

    print('  [1/4] 実データ読み込み (data_only=True)...')
    wb_data = openpyxl.load_workbook(xlsx_path, data_only=True)

    print('  [2/4] 式情報読み込み (data_only=False)...')
    wb_formula = openpyxl.load_workbook(xlsx_path, data_only=False)

    output_dir.mkdir(parents=True, exist_ok=True)

    print('  [3/4] CSV 出力中...')
    sheet_info = export_csv(wb_data, wb_formula, output_dir)
    print(f'         {len(sheet_info)} シートを出力しました')

    print('  [4/4] cells.json / metadata.json 出力中...')
    cells_path = export_cells_json(wb_formula, output_dir)
    meta_path = export_metadata(xlsx_path, sheet_info, cells_path, output_dir)

    print(f'\n完了: {output_dir}')
    print(f'  csv/           : {len(sheet_info)} ファイル')
    print(f'  cells.json     : {cells_path.stat().st_size:,} bytes')
    print(f'  metadata.json  : {meta_path.stat().st_size:,} bytes')


def main() -> None:
    parser = argparse.ArgumentParser(
        description='XLSX → CSV（実データ）+ JSON（式情報）変換ツール'
    )
    parser.add_argument('xlsx_file', type=Path, help='変換する XLSX ファイルのパス')
    parser.add_argument(
        '--output-dir', '-o', type=Path,
        help='出力ディレクトリ（省略時: outputs/<ファイル名>/ に自動生成）',
    )
    args = parser.parse_args()

    xlsx_path = args.xlsx_file.resolve()
    if not xlsx_path.exists():
        print(f'エラー: ファイルが見つかりません: {xlsx_path}', file=sys.stderr)
        sys.exit(1)
    if xlsx_path.suffix.lower() != '.xlsx':
        print(f'エラー: .xlsx ファイルを指定してください', file=sys.stderr)
        sys.exit(1)

    if args.output_dir:
        output_dir = args.output_dir.resolve()
    else:
        # デフォルト: スクリプトの1つ上の outputs/<ファイル名>/ フォルダ
        output_dir = xlsx_path.parent.parent / 'outputs' / xlsx_path.stem

    convert(xlsx_path, output_dir)


if __name__ == '__main__':
    main()
