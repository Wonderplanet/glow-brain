---
name: masterdata-csv-to-xlsx
description: 指定ディレクトリのマスタデータCSVをsheet_schemaの列定義に準拠した1つのXLSXファイルに統合します。「XLSX作成」「XLSXにまとめて」「マスタデータをエクセル出力」「CSVを統合」などのキーワードで使用します。
---

# マスタデータCSV → XLSX変換スキル

## 概要

指定ディレクトリにある**sheet_schema形式**のマスタデータCSVファイルを、`sheet_schema`の列定義に準拠した1つのXLSXファイルに統合します。

- **入力形式**: sheet_schema形式（3行ヘッダー）のCSVのみ受け付ける
- 1CSVファイル = 1シート
- 列順はsheet_schemaの3行目（ENABLE行）に従う
- i18n列（`.ja`などサフィックス付き列）は空欄で出力
- sheet_schemaが存在しないCSVはスキップ
- sheet_schema形式でないCSVはスキップ
- ヘッダー行のみ太字スタイル

> **前提条件**: 生成CSVを入力する場合は、事前に `masterdata-csv-to-sheet-schema-converter` スキルで sheet_schema 形式に変換してください。

## 基本的な使い方

```bash
python .claude/skills/masterdata-csv-to-xlsx/scripts/convert_to_xlsx.py \
  --input-dir <変換元CSVディレクトリ>
```

出力先は常に `{input-dir}/xlsx/{yyyyMMdd}_masterdata.xlsx`

## オプション

| 引数 | 必須 | デフォルト | 説明 |
|------|------|-----------|------|
| `--input-dir` | 必須 | - | 変換元CSVが置かれたディレクトリ |
| `--schema-dir` | 任意 | `projects/glow-masterdata/sheet_schema` | sheet_schemaのディレクトリ |
| `--output-filename` | 任意 | `{yyyyMMdd}_masterdata.xlsx` | 出力ファイル名 |

## ワークフロー

### Step 0: 対象CSVファイルの確認

変換対象のCSVファイルを確認します。**入力はsheet_schema形式であること**を前提とします。

```bash
ls <変換元CSVディレクトリ>/*.csv
```

> **生成CSV（形式A）を使う場合**: 先に `masterdata-csv-to-sheet-schema-converter` で変換してください。
> ```bash
> python .claude/skills/masterdata-csv-to-sheet-schema-converter/scripts/convert.py \
>   --input-dir <生成CSVディレクトリ>
> # → <生成CSVディレクトリ>/sheet_schema/ にsheet_schema形式CSVが出力される
> ```

### Step 1: 変換実行

```bash
python .claude/skills/masterdata-csv-to-xlsx/scripts/convert_to_xlsx.py \
  --input-dir <変換元CSVディレクトリ>
```

カスタムオプションを使う場合:

```bash
python .claude/skills/masterdata-csv-to-xlsx/scripts/convert_to_xlsx.py \
  --input-dir <変換元CSVディレクトリ> \
  --schema-dir projects/glow-masterdata/sheet_schema \
  --output-filename 20260220_masterdata.xlsx
```

### Step 2: 変換サマリー確認・ユーザーへ報告

スクリプトはJSON形式でサマリーを出力します。以下の内容をユーザーに報告します:

- 出力ファイルのパス
- 変換されたCSVファイル数・一覧
- スキップされたCSVファイル数・一覧（sheet_schemaなし）
- 合計ファイル数

## 出力サンプル

```json
{
  "output_file": "domain/tasks/my-task/xlsx/20260220_masterdata.xlsx",
  "converted": [
    "MstAbility.csv",
    "MstItem.csv"
  ],
  "skipped": [
    "MstAbilityI18n.csv",
    "MstItemI18n.csv"
  ],
  "summary": {
    "total": 4,
    "converted": 2,
    "skipped": 2
  }
}
```

## sheet_schema の構造

```
行1: memo（コメント行）
行2: TABLE,MstAbility,MstAbility,...（テーブル名）
行3: ENABLE,id,ability_type,...,description.ja,...（列定義）
```

- 列名に`.ja`が含まれる = I18nテーブルの列（空欄で出力）
- 先頭の`ENABLE`は除外して列定義として使用

## 注意事項

- **i18n列は空欄**: `.ja`などのサフィックス付き列はXLSX上で空欄になります（将来の拡張で対応予定）
- **I18nCSVはスキップ**: `MstAbilityI18n.csv`などはsheet_schemaが存在しないためスキップされます
- **シート名31文字制限**: Excelの制約によりシート名は31文字に切り詰められます
- **要`openpyxl`**: `pip install openpyxl` が必要です
