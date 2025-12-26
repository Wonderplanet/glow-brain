#!/usr/bin/env python3
"""
GLOW ID次番号検索スクリプト

指定されたカテゴリーと作品IDで使用可能な次のIDを提案します。
マスタデータ/GLOW_ID 管理ディレクトリのHTMLファイルを解析します。
"""

import re
import sys
import os
from typing import List, Set
from pathlib import Path
from html.parser import HTMLParser


class IDExtractor(HTMLParser):
    """HTMLからID文字列を抽出するパーサー"""

    def __init__(self):
        super().__init__()
        self.ids: Set[str] = set()
        self.current_data = ""

    def handle_data(self, data):
        self.current_data += data.strip()

    def handle_endtag(self, tag):
        if tag == "td":
            # テーブルセルの内容からIDパターンを抽出
            potential_id = self.current_data.strip()
            # 基本的なIDパターンにマッチするか確認
            if re.match(r"^[a-z_]+_[a-z]{3}_\d{5}$|^SBG_\d{3}_\d{3}$|^[a-z]{3}$", potential_id):
                self.ids.add(potential_id)
            self.current_data = ""


def extract_ids_from_html(html_file: Path) -> Set[str]:
    """HTMLファイルからIDを抽出"""
    parser = IDExtractor()
    try:
        with open(html_file, "r", encoding="utf-8") as f:
            parser.feed(f.read())
    except Exception as e:
        print(f"警告: {html_file.name} の読み込みに失敗: {e}", file=sys.stderr)
    return parser.ids


def find_existing_ids(
    base_dir: str, prefix: str, work_id: str
) -> List[int]:
    """
    既存IDを検索して番号部分のリストを返す

    Args:
        base_dir: マスタデータ/GLOW_ID 管理 ディレクトリのパス
        prefix: IDの接頭語（例: "chara", "quest_main_normal"）
        work_id: 作品ID（例: "spy", "dan"）

    Returns:
        既存の番号リスト（ソート済み）
    """
    base_path = Path(base_dir)
    all_ids: Set[str] = set()

    # HTMLファイルをすべて読み込み
    for html_file in base_path.glob("*.html"):
        if "削除予定" not in html_file.name:  # 削除予定ファイルは除外
            all_ids.update(extract_ids_from_html(html_file))

    # パターンに一致するIDから番号を抽出
    pattern = rf"^{re.escape(prefix)}_{re.escape(work_id)}_(\d{{5}})$"
    numbers = []

    for id_str in all_ids:
        match = re.match(pattern, id_str)
        if match:
            numbers.append(int(match.group(1)))

    return sorted(numbers)


def suggest_next_id(prefix: str, work_id: str, existing_numbers: List[int]) -> str:
    """
    次に使用可能なIDを提案

    Args:
        prefix: IDの接頭語
        work_id: 作品ID
        existing_numbers: 既存の番号リスト

    Returns:
        提案されるID
    """
    if not existing_numbers:
        # 初回IDは00001
        next_number = 1
    else:
        # 最大番号+1
        next_number = existing_numbers[-1] + 1

    return f"{prefix}_{work_id}_{next_number:05d}"


def main():
    """CLI実行時のエントリーポイント"""
    if len(sys.argv) < 4:
        print("使用法: python find_next_id.py <base_dir> <prefix> <work_id>")
        print("\n例:")
        print("  python find_next_id.py 'マスタデータ/GLOW_ID 管理' chara spy")
        print("  python find_next_id.py 'マスタデータ/GLOW_ID 管理' quest_main_normal dan")
        print("  python find_next_id.py 'マスタデータ/GLOW_ID 管理' background glo")
        sys.exit(1)

    base_dir = sys.argv[1]
    prefix = sys.argv[2]
    work_id = sys.argv[3]

    # ディレクトリ存在チェック
    if not os.path.isdir(base_dir):
        print(f"エラー: ディレクトリが見つかりません: {base_dir}", file=sys.stderr)
        sys.exit(1)

    # 既存ID検索
    print(f"検索中: {prefix}_{work_id}_*****")
    existing_numbers = find_existing_ids(base_dir, prefix, work_id)

    if existing_numbers:
        print(f"既存ID数: {len(existing_numbers)}")
        print(f"最新ID: {prefix}_{work_id}_{existing_numbers[-1]:05d}")
    else:
        print("既存IDなし（初回作成）")

    # 次のID提案
    next_id = suggest_next_id(prefix, work_id, existing_numbers)
    print(f"\n💡 提案ID: {next_id}")

    # 空き番号の確認（参考情報）
    if len(existing_numbers) > 1:
        gaps = []
        for i in range(len(existing_numbers) - 1):
            if existing_numbers[i + 1] - existing_numbers[i] > 1:
                gaps.append((existing_numbers[i] + 1, existing_numbers[i + 1] - 1))

        if gaps:
            print("\n📌 空き番号:")
            for start, end in gaps:
                if start == end:
                    print(f"  {prefix}_{work_id}_{start:05d}")
                else:
                    print(f"  {prefix}_{work_id}_{start:05d} 〜 {prefix}_{work_id}_{end:05d}")


if __name__ == "__main__":
    main()
