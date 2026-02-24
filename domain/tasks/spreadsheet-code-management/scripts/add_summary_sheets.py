#!/usr/bin/env python3
"""
MstPage・MstKomaLine サマリーシートを追加するスクリプト

「【ストーリー】必ず生きて帰る_コマ設計出力追加.xlsx」に「MstPage」「MstKomaLine」
サマリーシートを追加する。

各話シート（1話〜6話）のコマ設計右側（EB〜FU列）に出力されたデータを
1箇所にまとめて閲覧・コピーできるようにする。

各話シートのデータ位置（既存スクリプト add_koma_masterdata_output.py で設定済み）:
  - MstPage: 行31のみ  / EB(132)=id, EC(133)=release_key
  - MstKomaLine: 行31〜35 / EF(136)=id 〜 FU(177)=release_key

Usage:
    python3 scripts/add_summary_sheets.py
"""

import sys
from pathlib import Path

import openpyxl
from openpyxl.styles import PatternFill, Font
from openpyxl.utils import get_column_letter


# ---- 定数 -------------------------------------------------------------------

SCRIPT_DIR = Path(__file__).parent
BASE_DIR = SCRIPT_DIR.parent

# 対象XLSXパス（読み込み・上書き保存）
TARGET_XLSX = BASE_DIR / "xlsx" / "【ストーリー】必ず生きて帰る_コマ設計出力追加.xlsx"

# 対象シート名
TARGET_SHEETS = ["1話", "2話", "3話", "4話", "5話", "6話"]

# 各話シートの行番号（add_koma_masterdata_output.py と対応）
KOMA_DATA_START_ROW = 31  # コマ設計データ開始行
KOMA_DATA_END_ROW = 35    # コマ設計データ終了行

# MstPage 列番号
COL_EB = 132  # MstPage: id
COL_EC = 133  # MstPage: release_key

# MstKomaLine 列定義 (列番号, カラム名) - EF(136)〜FU(177)
MSTKOMALINE_COLS = [
    (136, "id"),
    (137, "mst_page_id"),
    (138, "row"),
    (139, "height"),
    (140, "koma_line_layout_asset_key"),
    (141, "koma1_asset_key"),
    (142, "koma1_width"),
    (143, "koma1_back_ground_offset"),
    (144, "koma1_effect_type"),
    (145, "koma1_effect_parameter1"),
    (146, "koma1_effect_parameter2"),
    (147, "koma1_effect_target_side"),
    (148, "koma1_effect_target_colors"),
    (149, "koma1_effect_target_roles"),
    (150, "koma2_asset_key"),
    (151, "koma2_width"),
    (152, "koma2_back_ground_offset"),
    (153, "koma2_effect_type"),
    (154, "koma2_effect_parameter1"),
    (155, "koma2_effect_parameter2"),
    (156, "koma2_effect_target_side"),
    (157, "koma2_effect_target_colors"),
    (158, "koma2_effect_target_roles"),
    (159, "koma3_asset_key"),
    (160, "koma3_width"),
    (161, "koma3_back_ground_offset"),
    (162, "koma3_effect_type"),
    (163, "koma3_effect_parameter1"),
    (164, "koma3_effect_parameter2"),
    (165, "koma3_effect_target_side"),
    (166, "koma3_effect_target_colors"),
    (167, "koma3_effect_target_roles"),
    (168, "koma4_asset_key"),
    (169, "koma4_width"),
    (170, "koma4_back_ground_offset"),
    (171, "koma4_effect_type"),
    (172, "koma4_effect_parameter1"),
    (173, "koma4_effect_parameter2"),
    (174, "koma4_effect_target_side"),
    (175, "koma4_effect_target_colors"),
    (176, "koma4_effect_target_roles"),
    (177, "release_key"),
]

# スタイル
HEADER_FILL_COLOR = "FFFFF2CC"  # 薄い黄色（各話シートと統一）
HEADER_FILL = PatternFill(patternType="solid", fgColor=HEADER_FILL_COLOR)
LINK_FONT = Font(color="0563C1", underline="single")  # ハイパーリンク文字スタイル


# ---- サマリーシート追加 ------------------------------------------------------

def add_mst_page_sheet(wb: openpyxl.Workbook, valid_sheets: list[str]) -> None:
    """
    「MstPage」サマリーシートを追加する。

    レイアウト:
      行1: ヘッダー（A=ソースシート, B=id, C=release_key）
      行2: 1話データ（A2=ハイパーリンク, B2='1話'!EB31, C2='1話'!EC31）
      行3: （空行）
      行4: 2話データ
      行5: （空行）
      ...
    """
    # 既存シートがあれば削除して再作成
    if "MstPage" in wb.sheetnames:
        del wb["MstPage"]
    ws = wb.create_sheet("MstPage")

    # ヘッダー行（行1）
    headers = ["ソースシート", "id", "release_key"]
    for col_idx, header in enumerate(headers, start=1):
        cell = ws.cell(row=1, column=col_idx)
        cell.value = header
        cell.fill = HEADER_FILL

    # 各話シートのデータ（データ行 + 空行）
    current_row = 2
    eb_letter = get_column_letter(COL_EB)  # "EB"
    ec_letter = get_column_letter(COL_EC)  # "EC"

    for sheet_name in valid_sheets:
        # A列: シート名をハイパーリンクで表示
        link_cell = ws.cell(row=current_row, column=1)
        link_cell.value = sheet_name
        link_cell.hyperlink = f"#'{sheet_name}'!A1"
        link_cell.font = LINK_FONT

        # B列: id (='1話'!EB31)
        ws.cell(row=current_row, column=2).value = (
            f"='{sheet_name}'!{eb_letter}{KOMA_DATA_START_ROW}"
        )
        # C列: release_key (='1話'!EC31)
        ws.cell(row=current_row, column=3).value = (
            f"='{sheet_name}'!{ec_letter}{KOMA_DATA_START_ROW}"
        )

        current_row += 2  # 1行空けて次の話へ

    print(f"  ✓ MstPageシート追加（{len(valid_sheets)}話分）")


def add_mst_koma_line_sheet(wb: openpyxl.Workbook, valid_sheets: list[str]) -> None:
    """
    「MstKomaLine」サマリーシートを追加する。

    レイアウト:
      行1: ヘッダー（A=ソースシート, B=id, C=mst_page_id, ...）
      行2: 1話/行31データ（A2=ハイパーリンク, B2='1話'!EF31, ...）
      行3: 1話/行32データ（A3=空, B3='1話'!EF32, ...）
      行4: 1話/行33データ
      行5: 1話/行34データ
      行6: 1話/行35データ
      行7: （空行）
      行8: 2話/行31データ（A8=ハイパーリンク, ...）
      ...
    """
    # 既存シートがあれば削除して再作成
    if "MstKomaLine" in wb.sheetnames:
        del wb["MstKomaLine"]
    ws = wb.create_sheet("MstKomaLine")

    # ヘッダー行（行1）
    # A列はソースシート、B列以降はMstKomaLineのカラム名
    all_headers = ["ソースシート"] + [col_name for _, col_name in MSTKOMALINE_COLS]
    for col_idx, header in enumerate(all_headers, start=1):
        cell = ws.cell(row=1, column=col_idx)
        cell.value = header
        cell.fill = HEADER_FILL

    # MstKomaLine列の列文字を事前計算
    col_letters = [
        (get_column_letter(src_col_num), col_name)
        for src_col_num, col_name in MSTKOMALINE_COLS
    ]

    # 各話シートのデータ（5行分 + 空行）
    current_row = 2
    koma_rows = range(KOMA_DATA_START_ROW, KOMA_DATA_END_ROW + 1)

    for sheet_name in valid_sheets:
        for koma_row in koma_rows:
            is_first_koma_row = (koma_row == KOMA_DATA_START_ROW)

            # A列: 最初の行にのみハイパーリンクを設定
            if is_first_koma_row:
                link_cell = ws.cell(row=current_row, column=1)
                link_cell.value = sheet_name
                link_cell.hyperlink = f"#'{sheet_name}'!A1"
                link_cell.font = LINK_FONT

            # B列以降: MstKomaLineカラムへの参照式
            for data_col_idx, (src_col_letter, _) in enumerate(col_letters, start=2):
                ws.cell(row=current_row, column=data_col_idx).value = (
                    f"='{sheet_name}'!{src_col_letter}{koma_row}"
                )

            current_row += 1

        # 空行を挿入して次の話シートを区切る
        current_row += 1

    koma_count = KOMA_DATA_END_ROW - KOMA_DATA_START_ROW + 1
    print(f"  ✓ MstKomaLineシート追加（{len(valid_sheets)}話 × {koma_count}行）")


# ---- メイン ------------------------------------------------------------------

def main() -> None:
    target_path = TARGET_XLSX.resolve()

    if not target_path.exists():
        print(f"エラー: XLSXが見つかりません: {target_path}", file=sys.stderr)
        sys.exit(1)

    print(f"対象: {target_path}")

    # [1] XLSXを読み込む（data_only=False で式を保持）
    print("\n[1/3] XLSXを読み込み中...")
    wb = openpyxl.load_workbook(str(target_path), data_only=False)

    # 対象シートの存在確認
    valid_sheets = [s for s in TARGET_SHEETS if s in wb.sheetnames]
    missing = [s for s in TARGET_SHEETS if s not in wb.sheetnames]
    if missing:
        print(f"  警告: 以下のシートが見つかりません: {missing}", file=sys.stderr)

    if not valid_sheets:
        print("エラー: 有効なシートが1つもありません。処理を中断します。", file=sys.stderr)
        sys.exit(1)

    # [2] サマリーシートを追加
    print("[2/3] サマリーシート追加中...")
    add_mst_page_sheet(wb, valid_sheets)
    add_mst_koma_line_sheet(wb, valid_sheets)

    # [3] 上書き保存
    print(f"\n[3/3] XLSX保存中: {target_path}")
    wb.save(str(target_path))

    size_kb = target_path.stat().st_size / 1024
    print(f"\n完了!")
    print(f"  ファイルサイズ: {size_kb:,.1f} KB")
    print(f"  出力先: {target_path}")
    print()
    print("追加されたシート:")
    print("  ・MstPage      - 全話のMstPageデータ（1話につき1行）")
    print("  ・MstKomaLine  - 全話のMstKomaLineデータ（1話につき5行）")
    print()
    print("A列のシート名をクリックすると対応する話シートへジャンプできます。")


if __name__ == "__main__":
    main()
