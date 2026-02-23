#!/usr/bin/env python3
"""
CSV / JSON → XLSX 再構築スクリプト

xlsx_to_csv_json.py で生成した outputs/<name>/ から XLSX を再構築する。
元の XLSX（xlsx/ フォルダ）は一切読まない・書かない。

出力先:
  outputs/<name>/<name>.xlsx   （--output で任意パス指定も可能）

Usage:
    python3 scripts/csv_json_to_xlsx.py outputs/【ストーリー】必ず生きて帰る
    python3 scripts/csv_json_to_xlsx.py outputs/【ストーリー】必ず生きて帰る --output my.xlsx
"""

import sys
import csv
import json
import argparse
from datetime import datetime
from pathlib import Path

import openpyxl


# ---- 型変換 ------------------------------------------------------------------

def parse_value(s: str):
    """CSV 文字列 → Python 型変換"""
    if s == '':
        return None
    if s == 'TRUE':
        return True
    if s == 'FALSE':
        return False
    # 日時
    try:
        return datetime.strptime(s, '%Y-%m-%d %H:%M:%S')
    except ValueError:
        pass
    # 整数
    try:
        return int(s)
    except ValueError:
        pass
    # 浮動小数点
    try:
        return float(s)
    except ValueError:
        pass
    # 文字列
    return s


# ---- formula_map 構築 --------------------------------------------------------

def build_formula_map(cells_json: Path) -> dict[str, dict[str, dict]]:
    """
    cells.json を読み込み、{ シート名: { 座標: cell_info } } を返す。
    """
    if not cells_json.exists():
        return {}

    with open(cells_json, encoding='utf-8') as f:
        cells_data: dict[str, list] = json.load(f)

    formula_map: dict[str, dict[str, dict]] = {}
    for sheet_name, cells in cells_data.items():
        sheet_formulas: dict[str, dict] = {}
        for cell in cells:
            coord = cell.get('coord')
            if coord:
                sheet_formulas[coord] = cell
        formula_map[sheet_name] = sheet_formulas

    return formula_map


# ---- 1シート分の再構築 -------------------------------------------------------

def write_sheet(ws, csv_path: Path, sheet_formulas: dict) -> None:
    """
    CSV を読み込んでシートに書き込む。
    cells.json に座標が存在するセルは formula 文字列で上書きする。

    formula_type に関わらず formula 文字列をそのまま書き込む:
      - normal      : =len(P3) など → openpyxl が式として扱う
      - computed_value : IFERROR(__xludf... パターン → 式文字列として保持
      - importrange : IMPORTRANGE ヘッダー → 式文字列として保持
    """
    if not csv_path.exists():
        print(f'    警告: CSV が見つかりません: {csv_path}', file=sys.stderr)
        return

    with open(csv_path, encoding='utf-8', newline='') as f:
        reader = csv.reader(f)
        for row_idx, row in enumerate(reader, start=1):
            for col_idx, raw_value in enumerate(row, start=1):
                cell = ws.cell(row=row_idx, column=col_idx)
                coord = cell.coordinate

                if coord in sheet_formulas:
                    # formula を書き込む（= で始まる文字列は openpyxl が式として扱う）
                    formula_str = sheet_formulas[coord].get('formula', '')
                    cell.value = formula_str
                else:
                    cell.value = parse_value(raw_value)


# ---- メイン処理 --------------------------------------------------------------

def reconstruct(outputs_dir: Path, output_path: Path) -> None:
    """
    outputs_dir/ の metadata.json・cells.json・csv/ から XLSX を再構築する。
    """
    meta_path = outputs_dir / 'metadata.json'
    cells_path = outputs_dir / 'cells.json'

    if not meta_path.exists():
        print(f'エラー: metadata.json が見つかりません: {meta_path}', file=sys.stderr)
        sys.exit(1)

    print(f'再構築開始: {outputs_dir.name}')

    # [1] metadata.json 読み込み
    print('  [1/4] metadata.json 読み込み...')
    with open(meta_path, encoding='utf-8') as f:
        metadata = json.load(f)
    sheets = metadata.get('sheets', [])
    print(f'         {len(sheets)} シートを検出')

    # [2] cells.json → formula_map
    print('  [2/4] cells.json 読み込み...')
    formula_map = build_formula_map(cells_path)
    formula_sheet_count = len(formula_map)
    formula_cell_count = sum(len(v) for v in formula_map.values())
    print(f'         {formula_sheet_count} シート、{formula_cell_count:,} セルに式情報あり')

    # [3] ワークブック構築
    print('  [3/4] シート再構築中...')
    wb = openpyxl.Workbook()
    # デフォルトの "Sheet" を削除
    if wb.active:
        wb.remove(wb.active)

    for sheet_info in sheets:
        sheet_name = sheet_info['sheet_name']
        csv_rel_path = sheet_info.get('file', '')
        csv_path = outputs_dir / csv_rel_path

        ws = wb.create_sheet(title=sheet_name)
        sheet_formulas = formula_map.get(sheet_name, {})

        write_sheet(ws, csv_path, sheet_formulas)
        print(f'    ✓ {sheet_name}')

    # [4] 保存
    print(f'  [4/4] XLSX 保存中: {output_path}')
    output_path.parent.mkdir(parents=True, exist_ok=True)
    wb.save(output_path)

    size_kb = output_path.stat().st_size / 1024
    print(f'\n完了: {output_path}')
    print(f'  シート数  : {len(sheets)}')
    print(f'  ファイルサイズ: {size_kb:,.1f} KB')


# ---- CLI エントリーポイント --------------------------------------------------

def main() -> None:
    parser = argparse.ArgumentParser(
        description='CSV/JSON → XLSX 再構築ツール（xlsx_to_csv_json.py の逆変換）'
    )
    parser.add_argument(
        'outputs_dir',
        type=Path,
        help='outputs/<name>/ ディレクトリのパス（metadata.json・cells.json・csv/ を含む）',
    )
    parser.add_argument(
        '--output', '-o',
        type=Path,
        default=None,
        help='出力 XLSX パス（省略時: outputs/<name>/<name>.xlsx）',
    )
    args = parser.parse_args()

    outputs_dir = args.outputs_dir.resolve()
    if not outputs_dir.is_dir():
        print(f'エラー: ディレクトリが見つかりません: {outputs_dir}', file=sys.stderr)
        sys.exit(1)

    if args.output:
        output_path = args.output.resolve()
    else:
        # デフォルト: outputs/<name>/<name>.xlsx
        output_path = outputs_dir / f'{outputs_dir.name}.xlsx'

    reconstruct(outputs_dir, output_path)


if __name__ == '__main__':
    main()
