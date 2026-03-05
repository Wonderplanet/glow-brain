---
name: masterdata-csv-validator
description: GLOWマスタデータCSVの検証スキル。作成したCSVファイルがDB投入可能か、server/client実装と整合性があるかをテンプレート、DBスキーマと照合してチェックします。マスタデータ、CSV検証、バリデーション、チェックで使用します。
---

# GLOWマスタデータCSV検証スキル

## 概要

作成したマスタデータCSVファイルがDB投入可能か、server/client実装の想定と整合性があるかを検証します。

### 2つの検証モード

| モード | 用途 | 参照先 |
|-------|------|-------|
| `sheet_schema`（デフォルト） | 新規作成CSVのテンプレート照合 | `projects/glow-masterdata/sheet_schema/` |
| `masterdata` | 既存マスタデータCSVとの列比較・自動修正 | `projects/glow-masterdata/` |

**`sheet_schema` モードの検証内容**:
1. **テンプレート一致**: 列順・列名がテンプレートCSVと一致するか（データ投入シートとの整合性）
2. **CSV形式**: 改行エスケープ、ダブルクォート等の形式が正しいか（DB投入可能性）
3. **必須カラム**: NULL不可カラムに値が設定されているか（DB制約違反防止）
4. **DBスキーマ整合性**: 型、enum値がスキーマと一致するか（実装との整合性）

**`masterdata` モードの検証内容**:
1. **ヘッダー形式**: 行1が `ENABLE,col1,col2,...` 形式か確認
2. **カラム名・順序**: 既存マスタデータとの列比較（欠損・余分・順序不一致を検出）
3. **目視確認用データ**: サンプルレコードと列の値例を出力（Claudeが列位置ずれを判断）
4. **自動修正**: カラム順序不一致を検出した場合にCSVを自動修正

## 基本的な使い方

### sheet_schemaモード（デフォルト）：新規作成CSVの検証

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv path/to/MstEvent.csv
```

### masterdataモード：既存マスタデータとの比較・自動修正

```bash
# 自動修正あり（デフォルト）
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv path/to/MstAbility.csv \
  --mode masterdata

# dry-runで修正内容だけ確認
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv path/to/MstAbility.csv \
  --mode masterdata \
  --dry-run

# 参照CSVを明示指定
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv path/to/MstAbility.csv \
  --mode masterdata \
  --reference-csv projects/glow-masterdata/MstAbility.csv
```

### 単一ファイルの検証（sheet_schemaモード）

**出力例（成功）**:
```json
{
  "file": "MstEvent.csv",
  "valid": true,
  "validations": {
    "template": {"valid": true, "issues": []},
    "format": {"valid": true, "issues": []},
    "schema": {"valid": true, "issues": []}
  },
  "summary": {
    "total_issues": 0,
    "critical_issues": 0,
    "warnings": 0
  }
}
```

**出力例（エラー）**:
```json
{
  "file": "OprGacha.csv",
  "valid": false,
  "validations": {
    "template": {
      "valid": false,
      "issues": [
        {
          "type": "column_order",
          "expected": ["id", "gacha_id", "name"],
          "actual": ["id", "name", "gacha_id"]
        }
      ]
    },
    "schema": {
      "valid": false,
      "issues": [
        {
          "type": "value_validation_error",
          "row": 8,
          "column": "gacha_type",
          "value": "Ultra",
          "message": "enum値 ['Normal', 'Special', 'Limited'] のいずれかが期待されますが、'Ultra' は許可されていません"
        }
      ]
    }
  },
  "summary": {
    "total_issues": 2,
    "critical_issues": 2,
    "warnings": 0
  }
}
```

### 個別の検証スクリプト

特定の検証のみ実行したい場合：

**テンプレート検証のみ**:
```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_template.py \
  --generated path/to/MstEvent.csv \
  --template projects/glow-masterdata/sheet_schema/MstEvent.csv
```

**CSV形式検証のみ**:
```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_csv_format.py \
  path/to/MstEvent.csv
```

**DBスキーマ検証のみ**:
```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_schema.py \
  --csv path/to/MstEvent.csv \
  --schema projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  --table mst_events
```

## ワークフロー

### Step 1: マスタデータ作成後の検証

マスタデータCSVを作成したら、DB投入前に必ず検証を実行します。

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
  --csv path/to/<ファイル名>.csv
```

### Step 2: エラーの確認

検証結果のJSONを確認し、`valid: false` の場合はエラー箇所を特定します。

**重要なフィールド**:
- `valid`: 全体の検証結果（true/false）
- `validations.<種別>.issues`: 各検証のエラー詳細
- `summary.critical_issues`: 致命的エラー数
- `summary.warnings`: 警告数

### Step 3: エラー修正

エラー種別に応じて修正します。詳細は [error-examples.md](references/error-examples.md) を参照してください。

**主なエラー種別**:
- `column_order`: カラム順序不一致 → テンプレートと一致させる
- `missing_column`: カラム欠損 → 不足カラムを追加
- `extra_column`: 余分なカラム → 不要カラムを削除
- `value_validation_error`: 値の検証エラー → 型・enum値を修正
- `invalid_header`: ヘッダー形式エラー → 1-3行目を修正

### Step 4: 再検証

修正後、再度検証を実行して `valid: true` になることを確認します。

## 複数ファイルの検証

ディレクトリ内の全CSVファイルを一括検証する場合：

```bash
for csv in path/to/masterdata/*.csv; do
  echo "Validating $csv..."
  python .claude/skills/masterdata-csv-validator/scripts/validate_all.py \
    --csv "$csv"
done
```

## テーブル名の推測ルール

CSVファイル名から自動的にテーブル名を推測します：

| CSVファイル名 | 推測されるテーブル名 |
|-------------|-----------------|
| `MstEvent.csv` | `mst_events` |
| `OprGacha.csv` | `opr_gachas` |
| `MstUnitI18n.csv` | `mst_units_i18n` |

手動でテーブル名を指定する場合は `--table` オプションを使用します。

## 検証スクリプト一覧

### scripts/validate_all.py（推奨）
統合検証スクリプト。`--mode` オプションでモードを切り替え可能。

**オプション:**
| オプション | 説明 |
|-----------|------|
| `--csv` | 検証対象CSVのパス（必須） |
| `--mode` | `sheet_schema`（デフォルト）または `masterdata` |
| `--reference-csv` | 参照CSVのパス（masterdataモード、省略時は自動推測） |
| `--dry-run` | masterdataモード: 修正内容のみ出力、CSVは書き換えない |

### scripts/validate_masterdata.py（masterdataモード専用）
既存マスタデータCSVとの比較・自動修正スクリプト。

**オプション:**
| オプション | 説明 |
|-----------|------|
| `--csv` | 検証対象CSVのパス（必須） |
| `--reference-csv` | 参照CSVのパス（省略時はファイル名から自動推測） |
| `--fix` / `--no-fix` | 自動修正の有効/無効（デフォルト: 有効） |
| `--dry-run` | 修正内容のみ出力、CSVは書き換えない |

### scripts/validate_template.py
sheet_schemaテンプレートCSVとの照合（列順・列名の一致確認）。

**オプション:**
| オプション | 説明 |
|-----------|------|
| `--csv` | 検証対象CSVのパス（`--generated` は非推奨） |
| `--reference-csv` | テンプレートCSVのパス（`--template` は非推奨） |

### scripts/validate_csv_format.py
CSV形式の正しさを検証（改行エスケープ、ダブルクォート等）。

### scripts/validate_schema.py
DBスキーマとの整合性を検証（型、enum値、NULL許可等）。

## リファレンス

詳細なルールとエラー修正方法：

- **検証ルール詳細**: [validation-rules.md](references/validation-rules.md)
- **よくあるエラーと修正方法**: [error-examples.md](references/error-examples.md)

## トラブルシューティング

### テンプレートファイルが見つからない

**エラー**:
```json
{
  "warning": "テンプレートファイルが見つかりません: projects/glow-masterdata/sheet_schema/MstEvent.csv",
  "skipped": true
}
```

**対処法**:
1. CSVファイル名がPascalCase + 単数形になっているか確認（例: `MstEvent.csv`, `OprGacha.csv`）
2. テンプレートディレクトリを確認:
   ```bash
   ls projects/glow-masterdata/sheet_schema/
   ```

### スキーマファイルが見つからない

**エラー**:
```json
{
  "warning": "スキーマファイルが見つかりません: projects/glow-server/api/database/schema/exports/master_tables_schema.json",
  "skipped": true
}
```

**対処法**:
パスが正しいか確認します。通常は `projects/glow-server/api/database/schema/exports/master_tables_schema.json` にあります。

### テーブル名が推測できない

**対処法**:
`--table` オプションで明示的にテーブル名を指定します（snake_case + 複数形）:

```bash
python .claude/skills/masterdata-csv-validator/scripts/validate_schema.py \
  --csv path/to/CustomFile.csv \
  --schema projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  --table mst_custom_tables
```

## 参照CSV自動推測ルール（masterdataモード）

`--reference-csv` を省略した場合、ファイル名から自動推測します：

| 検証対象CSV | 自動推測される参照CSV |
|-----------|-----------------|
| `/path/to/MstAbility.csv` | `projects/glow-masterdata/MstAbility.csv` |
| `/any/path/OprGacha.csv` | `projects/glow-masterdata/OprGacha.csv` |

`--reference-csv` を明示指定した場合はそちらを優先します。

## 注意事項

**sheet_schemaモード:**
- **検証は非破壊的**: CSVファイルを読み取るのみで、変更は行いません
- **エラー修正は手動**: 検証スクリプトはエラーを報告するのみで、自動修正は行いません
- **テンプレート最優先**: テンプレートCSVとDBスキーマが一致しない場合、テンプレートに従います

**masterdataモード:**
- **自動修正はデフォルトで有効**: カラム順序の不一致が検出された場合、CSVを自動修正します
- **バックアップなし**: Gitで管理されているため、修正前のバックアップは作成しません
- **dry-run推奨**: 初回は `--dry-run` で修正内容を確認してから実行することを推奨します
- **Claudeが内容を判断**: `inspection_data` に出力されるサンプルデータを確認し、列位置ずれの妥当性を判断します
