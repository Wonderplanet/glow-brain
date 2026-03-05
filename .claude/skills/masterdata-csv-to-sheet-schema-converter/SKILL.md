---
name: masterdata-csv-to-sheet-schema-converter
description: テーブル別マスタCSVを検証・修正しsheet_schema形式CSVに変換します。変換前のマスタデータCSVヘッダー検証・修正（行1）と、変換後のsheet_schema CSVヘッダー検証（行1-3）を自動実行。「sheet_schema変換」「マスタデータ変換」「CSV変換」「sheet_schema作成」などのキーワードで使用します。
---

# マスタデータCSV → sheet_schema CSV変換スキル

## 概要

指定ディレクトリにあるテーブル別マスタデータCSVを検証・修正し、`sheet_schema`形式CSVに変換します。

- **入力**: マスタデータCSV（`ENABLE,id,col1,...` の1行ヘッダー形式）
- **出力**: sheet_schema CSV（3行ヘッダー形式 + データ行）
- ヘッダー検証・列順修正を自動実行（in-place上書き）
- I18n列（`.ja` などのサフィックス付き）は空欄で出力
- 参照sheet_schemaが存在しないCSVはスキップ

## 基本的な使い方

```bash
python .claude/skills/masterdata-csv-to-sheet-schema-converter/scripts/convert.py \
  --input-dir <変換元CSVディレクトリ>
```

出力先は常に `{input-dir}/sheet_schema/` ディレクトリ

## オプション

| 引数 | 必須 | デフォルト | 説明 |
|------|------|-----------|------|
| `--input-dir` | 必須 | - | 変換元CSVが置かれたディレクトリ |
| `--output-dir` | 任意 | `{input-dir}/sheet_schema` | sheet_schema CSV出力先ディレクトリ |
| `--masterdata-ref-dir` | 任意 | `projects/glow-masterdata` | マスタデータCSV参照元ディレクトリ |
| `--schema-ref-dir` | 任意 | `projects/glow-masterdata/sheet_schema` | sheet_schema CSV参照元ディレクトリ |
| `--dry-run` | 任意 | false | 修正内容を表示するがファイルは書き込まない |

## ワークフロー

### Step 0: 対象CSVファイルの確認

変換対象のCSVファイルを確認します。

```bash
ls <変換元CSVディレクトリ>/*.csv
```

### Step 1: 変換実行

```bash
python .claude/skills/masterdata-csv-to-sheet-schema-converter/scripts/convert.py \
  --input-dir <変換元CSVディレクトリ>
```

dry-runで事前確認する場合:

```bash
python .claude/skills/masterdata-csv-to-sheet-schema-converter/scripts/convert.py \
  --input-dir <変換元CSVディレクトリ> \
  --dry-run
```

カスタムオプションを使う場合:

```bash
python .claude/skills/masterdata-csv-to-sheet-schema-converter/scripts/convert.py \
  --input-dir <変換元CSVディレクトリ> \
  --schema-ref-dir projects/glow-masterdata/sheet_schema \
  --masterdata-ref-dir projects/glow-masterdata
```

### Step 2: JSON結果を解析してユーザーへ報告

スクリプトはJSON形式でサマリーを stdout に出力します。以下の内容をユーザーに報告します:

- 修正されたテーブル一覧（`masterdata_validation.fixed == true`）
- 生成されたsheet_schema CSVの場所
- スキップされたテーブルとその理由
- 全体サマリー（合計数・修正数・生成数・スキップ数）

### Step 3: 次ステップへの案内

変換後のsheet_schema CSVは `masterdata-csv-to-xlsx` スキルへの入力として使用できます。

```bash
python .claude/skills/masterdata-csv-to-xlsx/scripts/convert_to_xlsx.py \
  --input-dir <変換元CSVディレクトリ>/sheet_schema
```

## 出力サンプル

```json
{
  "results": [
    {
      "table": "MstAbility",
      "masterdata_validation": {
        "valid": true,
        "issues": [],
        "fixed": false
      },
      "sheet_schema_output": "domain/tasks/my-task/sheet_schema/MstAbility.csv",
      "sheet_schema_validation": {
        "valid": true,
        "issues": []
      }
    },
    {
      "table": "MstItem",
      "masterdata_validation": {
        "valid": false,
        "issues": ["列順不一致: 参照='ENABLE,id,item_type,...' 入力='id,ENABLE,item_type,...'"],
        "fixed": true
      },
      "sheet_schema_output": "domain/tasks/my-task/sheet_schema/MstItem.csv",
      "sheet_schema_validation": {
        "valid": true,
        "issues": []
      }
    },
    {
      "table": "MstAbilityI18n",
      "skipped": true,
      "skip_reason": "参照sheet_schema CSVが見つかりません: projects/glow-masterdata/sheet_schema/MstAbilityI18n.csv"
    }
  ],
  "summary": {
    "total": 3,
    "masterdata_issues_found": 1,
    "masterdata_fixed": 1,
    "sheet_schema_generated": 2,
    "sheet_schema_valid": 2,
    "skipped": 1
  }
}
```

## sheet_schema の構造

```
行1: memo（コメント行。空か注釈テキスト）
行2: TABLE,MstAbility,MstAbility,...,MstAbilityI18n,...（列ごとの所属テーブル名）
行3: ENABLE,id,ability_type,...,description.ja,filter_title.ja,...（カラム定義）
行4以降: データ行
```

- 列名に`.ja`が含まれる = I18nテーブルの列（空欄で出力）
- 行1-3は参照sheet_schemaからそのままコピー
- データ行は参照マスタデータのカラム順にマッピング

## 注意事項

- **I18n列は空欄**: `.ja` などのサフィックス付き列は空欄で出力されます
- **I18n CSVはスキップ**: `MstAbilityI18n.csv` などはsheet_schemaが存在しないためスキップされます
- **余分な列は除外**: 入力CSVに参照にない列がある場合、警告してその列を除外します
- **欠損列は空欄補完**: 入力CSVに参照にある列が欠損している場合、警告して空列で補完します
- **in-place修正**: `--dry-run` なしで実行すると入力CSVのヘッダーが修正されます
