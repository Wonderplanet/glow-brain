---
name: masterdata-validator
description: 生成されたマスタデータCSVをスキーマJSONと照合し、整合性を検証・自動修正します。ENUM、NOT NULL、PRIMARY KEY制約をチェック。マスタデータの検証、スキーマとの整合性で使用。
allowed-tools: Read, Edit, Write, Bash(jq:*), Bash(cat:*), Bash(sed:*), Grep
argument-hint: CSVファイルパス モデル名
---

# マスタデータ 検証・修正スキル

このスキルは、生成されたマスタデータCSVファイルをスキーマJSONと照合し、整合性を検証・自動修正します。

マスタデータ-2プロンプトの「ステップ6: DDLスキーマとの整合性チェックと自動修正」を完全に実装しています。

## 入力

以下の2つの引数を受け取ります:
1. **CSVファイルパス**: 検証対象のCSVファイル（例: `マスタデータ/運用仕様書/新春ガチャ/OprGacha.csv`）
2. **モデル名**: モデル名（PascalCase形式、例: `OprGacha`, `MstUnit`）

## 出力

以下の情報をMarkdown形式で返します:

### 1. 検証結果サマリー
- 検証ステータス（✅ 問題なし / ⚠️ 警告あり / ❌ エラーあり）
- 検証日時
- 対象ファイル

### 2. 検証詳細
- 6-1. スキーマJSONファイルの参照結果
- 6-2. カラムの存在確認結果
- 6-3. データ型の検証結果
- 6-4. 制約の検証結果
- 6-5. 自動修正の実施内容
- 6-6. 修正ログ

### 3. 修正済みCSVファイル
検証・修正後のCSVファイルを同じパスに上書き保存します。

### 4. REPORT.md用の修正ログ
REPORT.mdの「スキーマ検証と修正」セクションに記載する内容を生成します。

## タスク

以下のステップで検証・修正を実行してください:

### ステップ6-1: スキーマJSONファイルの参照

```bash
# 補助スクリプトを使用してスキーマ取得の準備
# モデル名→テーブル名変換
bash .claude/skills/masterdata-schema-inspector/scripts/convert_model_to_table.sh <ModelName>

# スキーマJSONファイルのパス決定
# - mst_*, opr_* で始まる場合: master_tables_schema.json
# - usr_* で始まる場合: user_tables_schema.json
```

**使用するスキーマJSON**:
- マスタテーブル: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- ユーザーテーブル: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`

**テーブル定義の取得**:
```bash
# マスタテーブルの場合
jq '.databases.mst.tables.<table_name>' <schema_json_path>

# ユーザーテーブルの場合
jq '.databases.usr.tables.<table_name>' <schema_json_path>
```

### ステップ6-2: カラムの存在確認

**CSVヘッダーの取得**:
```bash
# CSVの3行目（ヘッダー行）を取得
sed -n '3p' <csv_file_path>
```

**チェック項目**:

1. **CSVにあってスキーマJSONにないカラム**
   - ❌ エラー: スキーマJSONに存在しないカラムは削除が必要
   - 該当カラムをCSVから削除して再保存
   - ⚠️ 注意: テンプレートファイルを使用している場合は通常発生しません

2. **スキーマJSONにあってCSVにないカラム**
   - NOT NULL制約があるカラム: CSVに追加してデフォルト値を設定
   - NULL許可のカラム: `__NULL__`で追加（任意）
   - ⚠️ 注意: テンプレートファイルを使用している場合は通常発生しません

3. **`__NULL__` の使用ルール**
   - ✅ **許可**: nullable=true のカラムのみ
   - ❌ **禁止**: nullable=false (NOT NULL) のカラムでは使用不可
   - NOT NULL列で未設定を表現したい場合は空文字列（何も書かない）を使用

4. **カラムの順序**
   - 順序は問わないが、`ENABLE`は常に1列目
   - `id`は2列目を推奨

**実装例**:
```bash
# スキーマJSONの全カラム名を取得
SCHEMA_COLUMNS=$(echo "$TABLE_SCHEMA" | jq -r '.columns | keys[]' | sort)

# CSVのヘッダーからカラム名を抽出
CSV_COLUMNS=$(sed -n '3p' <csv_file> | tr ',' '\n' | sort)

# 差分チェック
comm -23 <(echo "$CSV_COLUMNS") <(echo "$SCHEMA_COLUMNS")  # CSVにあってスキーマにない
comm -13 <(echo "$CSV_COLUMNS") <(echo "$SCHEMA_COLUMNS")  # スキーマにあってCSVにない
```

### ステップ6-3: データ型の検証

**チェック項目**:

1. **ENUM型**
   ```bash
   # スキーマJSONからENUM型カラムを抽出
   echo "$TABLE_SCHEMA" | jq -r '.columns | to_entries[] | select(.value.type | startswith("enum")) | .key + ": " + .value.type'
   ```

   - CSVの値が許可されたENUM値のみか確認
   - 不正な値があれば修正または削除
   - ENUM値の抽出: `enum('active','inactive','pending')` → `active`, `inactive`, `pending`

2. **INT/BIGINT型**
   - 数値のみが入っているか確認
   - 文字列が混入している場合は修正

3. **DATETIME型**
   - `YYYY-MM-DD HH:MM:SS`形式か確認
   - 形式が異なる場合は修正

4. **VARCHAR/TEXT型**
   - 最大文字数制限を超えていないか確認
   - 例: `varchar(255)` の場合、255文字以内

**実装例**:
```bash
# ENUM型の値を検証
for col in $(echo "$TABLE_SCHEMA" | jq -r '.columns | to_entries[] | select(.value.type | startswith("enum")) | .key'); do
    # ENUM値を抽出
    ENUM_VALUES=$(echo "$TABLE_SCHEMA" | jq -r ".columns.\"$col\".type" | sed -E "s/enum\('(.*)'\)/\1/" | tr ',' '\n')

    # CSVの該当カラムの値を取得してチェック
    # （実装は複雑なため、実際にはPythonやawkを使用推奨）
done
```

### ステップ6-4: 制約の検証

**PRIMARY KEY制約**:
```bash
# PRIMARY KEYカラムを取得
PRIMARY_KEY=$(echo "$TABLE_SCHEMA" | jq -r '.indexes.PRIMARY.columns[]')

# CSVの該当カラムの値が一意か確認
# （重複があれば❌ エラーとして報告）
```

**UNIQUE制約**:
```bash
# UNIQUE制約のインデックスを取得
UNIQUE_INDEXES=$(echo "$TABLE_SCHEMA" | jq -r '.indexes | to_entries[] | select(.value.unique == true) | .key')

# 各UNIQUEインデックスのカラムの値が一意か確認
```

**NOT NULL制約**:
```bash
# NOT NULL制約のカラムを取得
NOT_NULL_COLUMNS=$(echo "$TABLE_SCHEMA" | jq -r '.columns | to_entries[] | select(.value.nullable == false) | .key')

# CSVの該当カラムに`__NULL__`や空文字が入っていないか確認
# 違反がある場合はデフォルト値で埋める
```

### ステップ6-5: 自動修正の実施

検出された問題を自動修正します:

1. **不正なカラムの削除**
   - スキーマJSONに存在しないカラムをCSVから削除
   - 削除したカラム名を記録

2. **不足カラムの追加**
   - NOT NULL制約のあるカラムを追加
   - 適切なデフォルト値を設定

3. **データ型の修正**
   - ENUM値を許可された値に修正
   - 日時形式を正規化

4. **CSVの再保存**
   - 修正後のCSVを同じパスに上書き保存

**実装時の注意**:
- 元のCSVファイルのバックアップを作成することを推奨
- 修正前後の差分を記録

### ステップ6-6: 修正ログの記録

修正内容を以下の形式で記録（後でREPORTに含める）:

```markdown
## スキーマ検証と修正

### <ModelName>.csv
- ✅ スキーマチェック完了: 問題なし

### <ModelName2>.csv
- ⚠️ 修正内容:
  - 削除したカラム: `old_column` (スキーマJSONに存在しないため)
  - 追加したカラム: `new_required_field` (NOT NULL制約のため、デフォルト値: 0)
  - データ型修正: `status`カラムの不正値 "active_new" → "active"

### <ModelName3>.csv
- ❌ エラー:
  - PRIMARY KEY重複: id=12345 が2件存在
  - 対処: 2件目のidを12346に修正
```

## ベストプラクティス

### 検証スクリプトの活用

補助スクリプトを使用して効率的に検証を実行:
```bash
bash .claude/skills/masterdata-validator/scripts/validate_csv.sh <csv_file_path> <model_name>
```

出力はJSON形式で、以下の情報を含みます:
- レベル（INFO, WARNING, ERROR）
- メッセージ

### 段階的な検証アプローチ

1. まずスクリプトで自動検証を実行
2. WARNING/ERRORを確認
3. 必要に応じて手動で修正
4. 再度検証を実行して問題解消を確認

### テンプレートファイルの重要性

CSV作成時に`projects/glow-masterdata/sheet_schema/<ModelName>.csv`をコピーして使用していれば、ステップ6-2のエラーはほぼ発生しません。

### データ型検証の限界

このスキルでは基本的な検証を行いますが、以下の検証は手動確認を推奨します:
- ENUM値の妥当性（業務ロジック的に正しいか）
- 外部キー参照の整合性（他のマスタデータとの関連性）
- 日付範囲の妥当性（start_at < end_at など）

## 注意事項

- **参照専用**: `projects/` 配下のファイルは参照のみ。編集しないでください。
- **バックアップ推奨**: 自動修正を行う前に、元のCSVファイルのバックアップを作成してください。
- **テンプレートファイル厳守**: CSV作成時は必ずテンプレートファイルをコピーして使用してください。
- **手動確認の必要性**: スクリプトでは検出できない論理的な問題（業務ロジック違反など）は手動で確認してください。

## 検証結果の出力形式

### 成功時（問題なし）

```markdown
# OprGacha.csv スキーマ検証結果

## 検証ステータス: ✅ 問題なし

- **検証日時**: 2025-01-15 14:30:00
- **対象ファイル**: マスタデータ/運用仕様書/新春ガチャ/OprGacha.csv
- **モデル名**: OprGacha
- **テーブル名**: opr_gachas

## 検証詳細

### ステップ6-1: スキーマJSONファイルの参照
- ✅ スキーマファイルを正常に参照しました
- スキーマファイル: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- テーブル定義: 正常に取得

### ステップ6-2: カラムの存在確認
- ✅ CSVとスキーマJSONのカラムが一致しています
- カラム数: 23列

### ステップ6-3: データ型の検証
- ✅ ENUM型の値はすべて許可された値です
- ✅ INT/BIGINT型の値はすべて数値です
- ✅ DATETIME型の値はすべて正しい形式です

### ステップ6-4: 制約の検証
- ✅ PRIMARY KEY重複なし
- ✅ UNIQUE制約違反なし
- ✅ NOT NULL制約違反なし

### ステップ6-5: 自動修正の実施
- 修正なし

### ステップ6-6: 修正ログ
- 問題なし
```

### 警告/エラーあり

```markdown
# MstUnit.csv スキーマ検証結果

## 検証ステータス: ⚠️ 警告あり

- **検証日時**: 2025-01-15 14:35:00
- **対象ファイル**: マスタデータ/運用仕様書/新春ガチャ/MstUnit.csv
- **モデル名**: MstUnit
- **テーブル名**: mst_units

## 検証詳細

### ステップ6-2: カラムの存在確認
- ⚠️ CSVにあってスキーマJSONにないカラム: `old_field`
- ⚠️ スキーマJSONにあってCSVにないカラム: `new_required_field` (NOT NULL制約)

### ステップ6-3: データ型の検証
- ⚠️ ENUM型の不正値を検出: `status`カラムの値 "active_new" は許可されていません（許可値: active, inactive, pending）

### ステップ6-5: 自動修正の実施
- 削除したカラム: `old_field`
- 追加したカラム: `new_required_field` (デフォルト値: 0)
- データ型修正: `status`カラムの不正値 "active_new" → "active"

### ステップ6-6: 修正ログ
修正内容を記録しました。修正済みCSVを保存しました。
```

## 関連ファイル

- スクリプト: `.claude/skills/masterdata-validator/scripts/validate_csv.sh`
- スキーマJSON（マスタ）: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- スキーマJSON（ユーザー）: `projects/glow-server/api/database/schema/exports/user_tables_schema.json`
- 依存スキル: `masterdata-schema-inspector` (モデル名→テーブル名変換)

## 例: OprGacha の検証

### コマンド実行例

```bash
# 補助スクリプトを使用した検証
bash .claude/skills/masterdata-validator/scripts/validate_csv.sh \
    マスタデータ/運用仕様書/新春ガチャ/OprGacha.csv \
    OprGacha
```

### 期待される出力

JSON形式で検証結果が返されます:
```json
[
  {"level":"INFO","message":"CSV検証を開始します: マスタデータ/運用仕様書/新春ガチャ/OprGacha.csv (モデル: OprGacha)"},
  {"level":"INFO","message":"ステップ6-1: スキーマJSONファイルの参照"},
  {"level":"INFO","message":"テーブル名: opr_gachas"},
  ...
]
```

---

このスキルを使用することで、マスタデータCSVの整合性を自動的に検証・修正できます。
