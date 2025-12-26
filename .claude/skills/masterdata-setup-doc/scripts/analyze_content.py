#!/usr/bin/env python3
"""
コンテンツのマスタデータスキーマを分析し、構造化されたJSONを出力する。

Usage:
    python analyze_content.py <コンテンツ名> [--output <output_file>]

Example:
    python analyze_content.py "降臨バトル" --output /tmp/advent_battle_analysis.json
"""

import argparse
import json
import os
import re
import sys
from pathlib import Path
from typing import Dict, List, Any, Optional


class ContentAnalyzer:
    """マスタデータコンテンツ分析クラス"""

    def __init__(self, content_name: str):
        self.content_name = content_name
        self.base_dir = Path(__file__).resolve().parents[3]  # glow-brain/
        self.sheet_schema_dir = self.base_dir / "projects/glow-masterdata/sheet_schema"
        self.master_schema_file = self.base_dir / "projects/glow-server/api/database/schema/exports/master_tables_schema.json"

    def analyze(self) -> Dict[str, Any]:
        """コンテンツの全体分析を実行"""
        result = {
            "content_name": self.content_name,
            "sheets": [],
            "tables": [],
            "relationships": []
        }

        # 1. Sheet Schema分析
        sheets = self._find_related_sheets()
        result["sheets"] = sheets

        # 2. DB Tables分析
        tables = self._find_related_tables(sheets)
        result["tables"] = tables

        # 3. リレーション分析
        relationships = self._analyze_relationships(tables)
        result["relationships"] = relationships

        return result

    def _find_related_sheets(self) -> List[Dict[str, Any]]:
        """関連するsheet schemaファイルを検索"""
        sheets = []

        if not self.sheet_schema_dir.exists():
            return sheets

        # コンテンツ名からキーワードを抽出（例: "降臨バトル" -> "advent", "battle"）
        keywords = self._extract_keywords(self.content_name)

        for csv_file in self.sheet_schema_dir.glob("*.csv"):
            # ファイル名がキーワードに一致するか確認
            if self._matches_keywords(csv_file.stem, keywords):
                headers = self._read_csv_headers(csv_file)
                sheets.append({
                    "name": csv_file.stem,
                    "file": str(csv_file),
                    "headers": headers
                })

        return sheets

    def _find_related_tables(self, sheets: List[Dict[str, Any]]) -> List[Dict[str, Any]]:
        """関連するDBテーブルを検索"""
        tables = []

        if not self.master_schema_file.exists():
            return tables

        with open(self.master_schema_file, 'r', encoding='utf-8') as f:
            schema = json.load(f)

        # シート名からテーブル名を推測（例: MstAdventBattle -> mst_advent_battles）
        table_patterns = set()
        for sheet in sheets:
            table_name = self._sheet_to_table_name(sheet["name"])
            table_patterns.add(table_name)
            table_patterns.add(f"{table_name}_i18n")  # i18nテーブルも検索

        for table in schema.get("tables", []):
            table_name = table.get("name", "")
            if any(pattern in table_name for pattern in table_patterns):
                tables.append({
                    "name": table_name,
                    "columns": self._extract_columns(table),
                    "comment": table.get("comment", "")
                })

        return tables

    def _analyze_relationships(self, tables: List[Dict[str, Any]]) -> List[Dict[str, str]]:
        """テーブル間のリレーションを分析"""
        relationships = []

        for table in tables:
            for column in table["columns"]:
                # 外部キーっぽい列名を検出（mst_xxx_id パターン）
                if column["name"].startswith("mst_") and column["name"].endswith("_id"):
                    # 参照先テーブル名を推測
                    ref_table = column["name"].replace("_id", "s")  # 単純に複数形化
                    relationships.append({
                        "from_table": table["name"],
                        "from_column": column["name"],
                        "to_table": ref_table,
                        "to_column": "id"
                    })

        return relationships

    def _extract_keywords(self, content_name: str) -> List[str]:
        """コンテンツ名からキーワードを抽出"""
        # 簡易的な実装: 日本語->英語マッピング
        keyword_map = {
            "降臨": "advent",
            "バトル": "battle",
            "ガチャ": "gacha",
            "ミッション": "mission",
            "クエスト": "quest",
            "イベント": "event",
            "アイテム": "item",
            "キャラ": "unit",
            "装備": "equipment"
        }

        keywords = []
        for jp, en in keyword_map.items():
            if jp in content_name:
                keywords.append(en)

        return keywords

    def _matches_keywords(self, filename: str, keywords: List[str]) -> bool:
        """ファイル名がキーワードに一致するか"""
        filename_lower = filename.lower()
        return any(keyword.lower() in filename_lower for keyword in keywords)

    def _read_csv_headers(self, csv_file: Path) -> List[str]:
        """CSVファイルのヘッダーを読み取り"""
        try:
            with open(csv_file, 'r', encoding='utf-8') as f:
                first_line = f.readline().strip()
                return first_line.split(',')
        except Exception:
            return []

    def _sheet_to_table_name(self, sheet_name: str) -> str:
        """シート名からテーブル名を推測"""
        # MstAdventBattle -> mst_advent_battles
        # キャメルケースをスネークケースに変換
        s1 = re.sub('(.)([A-Z][a-z]+)', r'\1_\2', sheet_name)
        table_name = re.sub('([a-z0-9])([A-Z])', r'\1_\2', s1).lower()

        # 複数形化（簡易版）
        if not table_name.endswith('s'):
            table_name += 's'

        return table_name

    def _extract_columns(self, table: Dict[str, Any]) -> List[Dict[str, Any]]:
        """テーブルからカラム情報を抽出"""
        columns = []

        for col in table.get("columns", []):
            column_info = {
                "name": col.get("name", ""),
                "type": col.get("type", ""),
                "nullable": col.get("nullable", False),
                "default": col.get("default"),
                "comment": col.get("comment", "")
            }

            # ENUM型の場合、選択肢も含める
            if col.get("type") == "enum":
                column_info["enum_values"] = col.get("enum_values", [])

            columns.append(column_info)

        return columns


def main():
    parser = argparse.ArgumentParser(description="コンテンツのマスタデータスキーマを分析")
    parser.add_argument("content_name", help="コンテンツ名（例: 降臨バトル）")
    parser.add_argument("--output", "-o", help="出力ファイルパス（省略時は標準出力）")

    args = parser.parse_args()

    analyzer = ContentAnalyzer(args.content_name)
    result = analyzer.analyze()

    # JSON出力
    output_json = json.dumps(result, ensure_ascii=False, indent=2)

    if args.output:
        with open(args.output, 'w', encoding='utf-8') as f:
            f.write(output_json)
        print(f"分析結果を {args.output} に保存しました", file=sys.stderr)
    else:
        print(output_json)


if __name__ == "__main__":
    main()
