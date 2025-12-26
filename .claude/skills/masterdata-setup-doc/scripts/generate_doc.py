#!/usr/bin/env python3
"""
分析結果JSONからマスタデータ設定方法ドキュメント（Markdown）を生成する。

Usage:
    python generate_doc.py <analysis_json> <コンテンツ名> [--output <output_file>]

Example:
    python generate_doc.py /tmp/analysis.json "降臨バトル" --output マスタデータ/設定方法/降臨バトル.md
"""

import argparse
import json
import sys
from pathlib import Path
from typing import Dict, List, Any


class DocGenerator:
    """ドキュメント生成クラス"""

    TYPE_MAP = {
        "string": "文字列",
        "varchar": "文字列",
        "text": "文字列",
        "int": "整数",
        "integer": "整数",
        "bigint": "整数",
        "decimal": "少数",
        "float": "少数",
        "double": "少数",
        "datetime": "日時",
        "timestamp": "日時",
        "boolean": "真偽値",
        "enum": "列挙型"
    }

    EXCLUDE_FIELDS = {"ENABLE", "release_key", "created_at", "updated_at"}

    def __init__(self, analysis_data: Dict[str, Any], content_name: str):
        self.data = analysis_data
        self.content_name = content_name

    def generate(self) -> str:
        """ドキュメント全体を生成"""
        sections = [
            self._generate_header(),
            self._generate_toc(),
            self._generate_overview(),
            self._generate_tables_summary(),
            self._generate_table_details(),
            self._generate_examples(),
            self._generate_checklist()
        ]

        return "\n\n".join(filter(None, sections))

    def _generate_header(self) -> str:
        """ヘッダー生成"""
        return f"# {self.content_name} マスタデータ設定方法"

    def _generate_toc(self) -> str:
        """目次生成"""
        return """## 目次

1. [概要](#概要)
2. [{content_name}で使用するテーブル](#{content_name}で使用するテーブル)
3. [各テーブルの設定方法](#各テーブルの設定方法)
4. [設定例](#設定例)
5. [注意事項とチェックポイント](#注意事項とチェックポイント)

---""".format(content_name=self.content_name)

    def _generate_overview(self) -> str:
        """概要セクション生成"""
        return f"""## 概要

{self.content_name}のマスタデータ設定について説明します。

（このセクションは手動で詳細を追加してください）

---"""

    def _generate_tables_summary(self) -> str:
        """テーブル一覧セクション生成"""
        sheets = self.data.get("sheets", [])
        tables = self.data.get("tables", [])

        if not sheets:
            return ""

        lines = [f"## {self.content_name}で使用するテーブル"]
        lines.append("")
        lines.append("| シート | 対応するDBテーブル | 用途 |")
        lines.append("|-------|------------------|------|")

        for sheet in sheets:
            sheet_name = sheet["name"]
            # シートに対応するテーブルを検索
            related_tables = [t["name"] for t in tables if self._is_related(sheet_name, t["name"])]

            if related_tables:
                table_names = "<br>".join([f"`{t}`" for t in related_tables])
                lines.append(f"| **{sheet_name}** | {table_names} | （用途を手動で記載） |")

        lines.append("")
        lines.append("---")

        return "\n".join(lines)

    def _generate_table_details(self) -> str:
        """各テーブルの詳細セクション生成"""
        sheets = self.data.get("sheets", [])

        if not sheets:
            return ""

        lines = ["## 各テーブルの設定方法"]
        lines.append("")

        for i, sheet in enumerate(sheets, 1):
            lines.append(f"### {i}. {sheet['name']}")
            lines.append("")
            lines.append("（テーブルの説明を手動で記載）")
            lines.append("")
            lines.append("#### 列の説明")
            lines.append("")
            lines.append("| 列名 | 型 | NULL許容 | 説明 | 設定例 |")
            lines.append("|------|-----|---------|------|--------|")

            # テーブル定義から列情報を取得
            table_columns = self._get_table_columns_for_sheet(sheet["name"])

            for col in table_columns:
                if col["name"] in self.EXCLUDE_FIELDS:
                    continue

                col_name = f"**{col['name']}**"
                col_type = self._format_type(col)
                nullable = "○" if col.get("nullable", False) else ""
                description = col.get("comment", "（説明を記載）")
                example = "（例を記載）"

                lines.append(f"| {col_name} | {col_type} | {nullable} | {description} | {example} |")

            lines.append("")
            lines.append("---")
            lines.append("")

        return "\n".join(lines)

    def _generate_examples(self) -> str:
        """設定例セクション生成"""
        return f"""## 設定例

実際の{self.content_name}を想定した設定例を示します。

（このセクションは手動でMarkdownテーブル形式の設定例を追加してください）

---"""

    def _generate_checklist(self) -> str:
        """チェックリストセクション生成"""
        return """## 注意事項とチェックポイント

### 必須確認事項

#### 1. IDの一意性

- [ ] すべてのテーブルで `id` フィールドは一意であること
- [ ] 他のコンテンツとIDが重複しないこと

#### 2. 外部キーの整合性

- [ ] 外部キー参照先のIDが存在すること
- [ ] リレーションが正しく設定されていること

#### 3. 開催期間の設定（該当する場合）

- [ ] `start_at` < `end_at` であること
- [ ] 日時フォーマットは `YYYY-MM-DD HH:MM:SS` （JSTタイムゾーン）

#### 4. ENUM型の選択肢

- [ ] ENUM型の値が定義された選択肢内であること

#### 5. NULL制約の確認

- [ ] NOT NULL列に空値が設定されていないこと

（追加のチェック項目を手動で記載してください）"""

    def _is_related(self, sheet_name: str, table_name: str) -> bool:
        """シートとテーブルが関連しているか判定"""
        # 簡易的な判定: シート名の小文字版がテーブル名に含まれるか
        sheet_lower = sheet_name.lower().replace("mst", "")
        table_lower = table_name.lower().replace("mst_", "").replace("_i18n", "")

        return sheet_lower in table_lower or table_lower in sheet_lower

    def _get_table_columns_for_sheet(self, sheet_name: str) -> List[Dict[str, Any]]:
        """シートに対応するテーブルのカラム一覧を取得"""
        tables = self.data.get("tables", [])

        columns = []
        for table in tables:
            if self._is_related(sheet_name, table["name"]):
                columns.extend(table.get("columns", []))

        return columns

    def _format_type(self, column: Dict[str, Any]) -> str:
        """型を非エンジニア向けに変換"""
        col_type = column.get("type", "")
        formatted = self.TYPE_MAP.get(col_type, col_type)

        # ENUM型の場合、選択肢も記載
        if col_type == "enum":
            enum_values = column.get("enum_values", [])
            if enum_values:
                formatted += f" (`{'`, `'.join(enum_values)}`)"

        return formatted


def main():
    parser = argparse.ArgumentParser(description="マスタデータ設定方法ドキュメントを生成")
    parser.add_argument("analysis_json", help="分析結果JSONファイル")
    parser.add_argument("content_name", help="コンテンツ名（例: 降臨バトル）")
    parser.add_argument("--output", "-o", help="出力ファイルパス（省略時は標準出力）")

    args = parser.parse_args()

    # 分析結果JSONを読み込み
    with open(args.analysis_json, 'r', encoding='utf-8') as f:
        analysis_data = json.load(f)

    # ドキュメント生成
    generator = DocGenerator(analysis_data, args.content_name)
    doc_content = generator.generate()

    # 出力
    if args.output:
        output_path = Path(args.output)
        output_path.parent.mkdir(parents=True, exist_ok=True)
        with open(output_path, 'w', encoding='utf-8') as f:
            f.write(doc_content)
        print(f"ドキュメントを {args.output} に保存しました", file=sys.stderr)
    else:
        print(doc_content)


if __name__ == "__main__":
    main()
