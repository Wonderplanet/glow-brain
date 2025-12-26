#!/usr/bin/env python3
"""
ExcelファイルをシートごとにCSVファイルに変換するスクリプト
"""
import sys
import os
from pathlib import Path
import pandas as pd


def xlsx_to_csv(xlsx_path: str, output_dir: str = None):
    """
    Excelファイルの各シートをCSVファイルに変換

    Args:
        xlsx_path: Excelファイルのパス
        output_dir: 出力ディレクトリ（指定しない場合はExcelファイルと同じディレクトリ）
    """
    # ファイルの存在確認
    if not os.path.exists(xlsx_path):
        print(f"エラー: ファイルが見つかりません: {xlsx_path}")
        sys.exit(1)

    # 出力ディレクトリの設定
    if output_dir is None:
        output_dir = os.path.dirname(xlsx_path)

    # 出力ディレクトリの作成
    os.makedirs(output_dir, exist_ok=True)

    # Excelファイルのベース名を取得
    base_name = Path(xlsx_path).stem

    print(f"Excelファイルを読み込んでいます: {xlsx_path}")

    try:
        # Excelファイルを読み込み（全シート）
        excel_file = pd.ExcelFile(xlsx_path)

        print(f"\n検出されたシート数: {len(excel_file.sheet_names)}")
        print(f"シート名: {', '.join(excel_file.sheet_names)}\n")

        # 各シートをCSVに変換
        for sheet_name in excel_file.sheet_names:
            print(f"処理中: {sheet_name}")

            # シートを読み込み
            df = pd.read_excel(excel_file, sheet_name=sheet_name)

            # CSVファイル名を作成（シート名をファイル名に使用）
            # ファイル名に使えない文字を置換
            safe_sheet_name = sheet_name.replace('/', '_').replace('\\', '_').replace(':', '_')
            csv_filename = f"{base_name}_{safe_sheet_name}.csv"
            csv_path = os.path.join(output_dir, csv_filename)

            # CSVに出力
            df.to_csv(csv_path, index=False, encoding='utf-8-sig')
            print(f"  → 保存完了: {csv_path} ({len(df)}行, {len(df.columns)}列)")

        print(f"\n✓ 変換が完了しました。出力先: {output_dir}")

    except Exception as e:
        print(f"エラー: {e}")
        sys.exit(1)


if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("使用方法: python xlsx_to_csv.py <Excelファイルのパス> [出力ディレクトリ]")
        sys.exit(1)

    xlsx_path = sys.argv[1]
    output_dir = sys.argv[2] if len(sys.argv) > 2 else None

    xlsx_to_csv(xlsx_path, output_dir)
