---
name: masterdata-schema-inspector
description: マスタデータのスキーマ情報を調査・提示。モデル名からテーブル定義、ENUM選択肢、制約情報を抽出します。マスタデータのスキーマ、テーブル定義、CSV形式で使用。
allowed-tools: Read, Bash(jq:*), Bash(cat:*), Bash(head:*), Bash(tail:*), Grep, Glob
argument-hint: モデル名（例: OprGacha, MstUnit, MstAdventBattle）
---

# マスタデータ スキーマ調査スキル

このスキルは、プロジェクトのマスタデータモデルのスキーマ情報を調査し、以下の情報を提示します:

- テーブル定義（カラム名、データ型、制約）
- ENUM型の選択肢
- NOT NULL制約、デフォルト値
- CSVテンプレートファイルの構造
- 既存マスタデータのパターン

## 入力

モデル名を受け取ります（PascalCase形式）:
- 例: `OprGacha`, `MstUnit`, `MstAdventBattle`, `MstAbility`

## 出力

以下の情報をMarkdown形式で整形して返します:

### 1. モデル情報サマリー
- モデル名
- テーブル名（変換結果）
- データベース種別（マスタ/ユーザー）

### 2. テーブル定義
- テーブルコメント（説明）
- 全カラムの情報（カラム名、型、NULL可否、デフォルト値、コメント）
- PRIMARY KEY情報
- UNIQUE制約
- インデックス情報

### 3. ENUM型の詳細
- ENUM型カラムの一覧
- 各ENUM型の選択肢

### 4. CSVテンプレートファイル構造
- ヘッダー行（1行目: memo、2行目: TABLE指定、3行目: 実際のヘッダー）
- カラム数
- I18n対応カラムの有無

### 5. 既存マスタデータのパターン
- 既存データの件数
- IDの範囲（最小〜最大）
- 代表的なデータ例（先頭3件）

## タスク

以下のステップで調査を実行してください:

### ステップ1: モデル名の検証とテーブル名変換

```bash
# モデル名をテーブル名に変換
bash .claude/skills/masterdata-schema-inspector/scripts/convert_model_to_table.sh <ModelName>
```

### ステップ2: スキーマJSONからテーブル定義を取得

```bash
# テーブル定義をJSON形式で取得
bash .claude/skills/masterdata-schema-inspector/scripts/extract_schema.sh <table_name>
```

### ステップ3: カラム情報の解析

取得したテーブル定義JSONから以下を抽出:

**カラム情報**:
```bash
# 全カラム名を取得
echo '<table_def_json>' | jq -r '.columns | keys[]'

# 特定カラムの詳細情報を取得
echo '<table_def_json>' | jq -r '.columns["カラム名"]'
```

**ENUM型の解析**:
```bash
# ENUM型のカラムを抽出
echo '<table_def_json>' | jq -r '.columns | to_entries[] | select(.value.type | startswith("enum")) | .key + ": " + .value.type'
```

**制約情報**:
```bash
# PRIMARY KEY情報
echo '<table_def_json>' | jq -r '.indexes.PRIMARY'

# NOT NULL制約のカラム一覧
echo '<table_def_json>' | jq -r '.columns | to_entries[] | select(.value.nullable == false) | .key'
```

### ステップ4: CSVテンプレートファイルの読み取り

```bash
# テンプレートファイルのパス
TEMPLATE_FILE="projects/glow-masterdata/sheet_schema/<ModelName>.csv"

# テンプレートファイルの存在確認
if [ -f "$TEMPLATE_FILE" ]; then
    # ヘッダー3行を表示
    head -3 "$TEMPLATE_FILE"

    # カラム数をカウント（3行目のカンマ区切りで判定）
    head -3 "$TEMPLATE_FILE" | tail -1 | awk -F',' '{print NF}'
fi
```

### ステップ5: 既存マスタデータの参照

```bash
# 既存マスタデータのパス
MASTER_DATA_FILE="projects/glow-masterdata/<ModelName>.csv"

# 既存データの存在確認
if [ -f "$MASTER_DATA_FILE" ]; then
    # 件数カウント（ヘッダーを除く）
    tail -n +2 "$MASTER_DATA_FILE" | wc -l

    # 先頭3件のデータを表示
    head -5 "$MASTER_DATA_FILE"
fi
```

### ステップ6: 結果の整形と出力

以下のMarkdown形式で結果をまとめてください:

```markdown
# <ModelName> スキーマ調査結果

## モデル情報

- **モデル名**: <ModelName>
- **テーブル名**: <table_name>
- **データベース**: <mst|usr>
- **テーブル説明**: <comment>

## カラム定義

| カラム名 | 型 | NULL可 | デフォルト値 | 説明 |
|---------|-----|--------|-------------|------|
| id | varchar(255) | NO | - | UUID |
| ... | ... | ... | ... | ... |

## ENUM型カラム

### <column_name>
- 型: `enum('value1','value2','value3')`
- 選択肢:
  - `value1`
  - `value2`
  - `value3`

## 制約情報

### PRIMARY KEY
- カラム: `id`

### NOT NULL制約
- `id`
- `column1`
- `column2`

### UNIQUE制約
- なし（または制約の詳細）

## CSVテンプレートファイル

- **ファイルパス**: `projects/glow-masterdata/sheet_schema/<ModelName>.csv`
- **ヘッダー構造**:
  - 1行目: `memo`
  - 2行目: `TABLE,<ModelName>,<ModelName>,...`（カラム毎のテーブル所属）
  - 3行目: `ENABLE,id,column1,column2,...`（実際のヘッダー）
- **カラム数**: <N>列
- **I18n対応**: <有|無>

## 既存マスタデータ

- **ファイルパス**: `projects/glow-masterdata/<ModelName>.csv`
- **件数**: <N>件
- **ID範囲**: <min_id> 〜 <max_id>（※存在する場合）

### 代表的なデータ例（先頭3件）

```csv
ENABLE,id,column1,column2,...
e,id_001,value1,value2,...
e,id_002,value1,value2,...
e,id_003,value1,value2,...
```

## データ設計のヒント

### 必須カラム（NOT NULL）
以下のカラムは必ず値を設定する必要があります:
- `id`: 一意のID（UUID形式推奨）
- `column1`: <説明>
- `column2`: <説明>

### ENUM型の値
以下のENUM型カラムは、指定された値のみ使用可能です:
- `<column_name>`: `value1`, `value2`, `value3`

### デフォルト値
以下のカラムにはデフォルト値が設定されています:
- `<column_name>`: <default_value>

### I18n対応
このモデルはI18n対応が必要です。対応する`<ModelName>I18n.csv`も作成してください。（※該当する場合）
```

## ベストプラクティス

### スキーマJSONファイルの参照方法

**マスタテーブルの場合**:
```bash
# テーブル一覧
jq '.databases.mst.tables | keys' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定テーブルのスキーマ
jq '.databases.mst.tables.opr_gachas' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# カラム一覧のみ
jq '.databases.mst.tables.opr_gachas.columns | keys' projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 特定カラムの詳細
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**ユーザーテーブルの場合**:
```bash
# テーブル一覧
jq '.databases.usr.tables | keys' projects/glow-server/api/database/schema/exports/user_tables_schema.json

# 特定テーブルのスキーマ
jq '.databases.usr.tables.usr_users' projects/glow-server/api/database/schema/exports/user_tables_schema.json
```

### モデル名→テーブル名変換規則

- `OprGacha` → `opr_gachas`（PascalCase → snake_case + 複数形）
- `MstUnit` → `mst_units`
- `MstAdventBattle` → `mst_advent_battles`
- `MstAbility` → `mst_abilities`（y → ies）

### CSVテンプレートファイルの重要性

CSV作成時は必ず以下のテンプレートファイルをコピーして使用してください:
```bash
cp projects/glow-masterdata/sheet_schema/<ModelName>.csv マスタデータ/運用仕様書/<施策名>/<ModelName>.csv
```

テンプレートファイルのヘッダー（3行目）に完全に従うことが重要です。勝手にカラムを追加・削除しないでください。

## 注意事項

- **参照専用**: `projects/` 配下のファイルは参照のみ。編集しないでください。
- **テーブル名が見つからない場合**: モデル名のスペルミスや複数形化のルール違反がないか確認してください。
- **ENUM型の厳守**: ENUM型カラムには、定義された値以外を使用できません。
- **I18n対応の確認**: テンプレートファイルの2行目に`<ModelName>I18n`が含まれている場合、I18n対応が必要です。

## 関連ファイル

- スクリプト1: `.claude/skills/masterdata-schema-inspector/scripts/convert_model_to_table.sh`
- スクリプト2: `.claude/skills/masterdata-schema-inspector/scripts/extract_schema.sh`
- スキーマJSON（マスタ）: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- スキーマJSON（ユーザー）: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`
- CSVテンプレート: `projects/glow-masterdata/sheet_schema/*.csv`
- 既存マスタデータ: `projects/glow-masterdata/*.csv`

## 例: OprGacha の調査

### コマンド実行例

```bash
# 1. テーブル名変換
bash .claude/skills/masterdata-schema-inspector/scripts/convert_model_to_table.sh OprGacha
# 出力: opr_gachas

# 2. スキーマ取得
bash .claude/skills/masterdata-schema-inspector/scripts/extract_schema.sh opr_gachas
# 出力: JSON形式のテーブル定義

# 3. テンプレートファイル確認
head -3 projects/glow-masterdata/sheet_schema/OprGacha.csv

# 4. 既存データ確認
head -5 projects/glow-masterdata/OprGacha.csv
```

### 期待される出力

```markdown
# OprGacha スキーマ調査結果

## モデル情報

- **モデル名**: OprGacha
- **テーブル名**: opr_gachas
- **データベース**: mst
- **テーブル説明**: ガチャ運用マスタ

## カラム定義

| カラム名 | 型 | NULL可 | デフォルト値 | 説明 |
|---------|-----|--------|-------------|------|
| id | varchar(255) | NO | - | ガチャID |
| gacha_type | varchar(255) | NO | None | ガチャタイプ |
| release_key | bigint | NO | 1 | リリースキー |
| ... | ... | ... | ... | ... |

（以下略）
```

---

このスキルを使用することで、マスタデータのスキーマ情報を効率的に調査できます。
