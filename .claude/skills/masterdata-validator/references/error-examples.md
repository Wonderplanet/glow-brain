# よくあるエラーと修正方法

## テンプレート関連エラー

### カラム順序不一致

**エラー例**
```json
{
  "type": "column_order",
  "expected": ["id", "event_id", "name", "description"],
  "actual": ["id", "name", "event_id", "description"]
}
```

**原因**: カラムの順序がテンプレートと異なる

**修正方法**:
1. テンプレートCSV（`projects/glow-masterdata/sheet_schema/`）を確認
2. 3行目のカラム順序をテンプレートと完全一致させる

### 欠損カラム

**エラー例**
```json
{
  "type": "missing_column",
  "column": "sort_order"
}
```

**原因**: テンプレートに存在するカラムがCSVにない

**修正方法**:
1. テンプレートから該当カラムをコピー
2. CSVの3行目に追加
3. データ行にも対応する値を追加（NULL許可なら空でOK）

### 余分なカラム

**エラー例**
```json
{
  "type": "extra_column",
  "column": "temp_field"
}
```

**原因**: テンプレートに存在しないカラムがCSVにある

**修正方法**:
1. 不要なカラムを削除
2. または、テンプレートが古い場合は最新テンプレートを確認

## CSV形式エラー

### セル内の実際の改行

**エラー例**
```json
{
  "type": "actual_newline_in_cell",
  "severity": "warning",
  "row": 5,
  "column": 3,
  "message": "セル内に実際の改行文字が含まれています。\\nエスケープシーケンスの使用を推奨します。"
}
```

**修正方法**:
```csv
# 修正前（誤り）
id,description
1,"これは
改行を含む
テキストです"

# 修正後（正しい）
id,description
1,"これは\\n改行を含む\\nテキストです"
```

実際の改行を `\\n` エスケープシーケンスに置換します。

### ヘッダー形式エラー

**エラー例**
```json
{
  "type": "invalid_header",
  "row": 1,
  "expected": "memo",
  "actual": "Meta"
}
```

**修正方法**:
1. 1行目の最初のセルを `memo` に修正
2. 2行目の最初のセルを `TABLE,<テーブル名>` 形式に修正
3. 3行目の最初のセルを `ENABLE` に修正

## DBスキーマエラー

### 型不一致（整数型）

**エラー例**
```json
{
  "type": "value_validation_error",
  "row": 10,
  "column": "sort_order",
  "value": "abc",
  "message": "整数型が期待されますが、'abc' は整数ではありません",
  "column_type": "int"
}
```

**修正方法**:
`abc` を数値（例: `1`, `100`）に変更します。

### 型不一致（日時型）

**エラー例**
```json
{
  "type": "value_validation_error",
  "row": 7,
  "column": "start_at",
  "value": "2024/01/15 10:00:00",
  "message": "日時形式（YYYY-MM-DD HH:MM:SS）が期待されますが、'2024/01/15 10:00:00' は形式が異なります"
}
```

**修正方法**:
```csv
# 修正前
2024/01/15 10:00:00

# 修正後
2024-01-15 10:00:00
```

スラッシュ（`/`）をハイフン（`-`）に変更します。

### enum値不正

**エラー例**
```json
{
  "type": "value_validation_error",
  "row": 8,
  "column": "gacha_type",
  "value": "Ultra",
  "message": "enum値 ['Normal', 'Special', 'Limited'] のいずれかが期待されますが、'Ultra' は許可されていません"
}
```

**修正方法**:
1. DBスキーマで許可されているenum値を確認
   ```bash
   jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
     projects/glow-server/api/database/schema/exports/master_tables_schema.json
   ```
2. 許可された値のいずれかに変更（例: `Normal`, `Special`, `Limited`）

### NULL不可カラムの空値

**エラー例**
```json
{
  "type": "value_validation_error",
  "row": 12,
  "column": "name",
  "value": "",
  "message": "NULL不可カラムに空値が設定されています",
  "nullable": false
}
```

**修正方法**:
該当カラムに適切な値を設定します。空値は許可されません。

## カラム数不一致

**エラー例**
```json
{
  "type": "column_count_mismatch",
  "expected": 10,
  "actual": 9,
  "message": "カラム数が一致しません（期待: 10, 実際: 9）"
}
```

**原因**: データ行のカラム数がヘッダーと異なる

**修正方法**:
1. ヘッダー行（3行目）のカラム数を確認
2. 各データ行が同じカラム数になるよう調整
3. 不足している場合は空値を追加（NULL許可カラムのみ）

## テンプレート/スキーマファイル未発見

**エラー例**
```json
{
  "valid": false,
  "warning": "テンプレートファイルが見つかりません: projects/glow-masterdata/sheet_schema/MstEvent.csv",
  "skipped": true
}
```

**原因**:
1. CSVファイル名がテーブル名と一致していない
2. テンプレートファイルが存在しない

**修正方法**:
1. ファイル名を確認（例: `MstEvent.csv`, `OprGacha.csv`）
2. テンプレートディレクトリを確認
   ```bash
   ls projects/glow-masterdata/sheet_schema/
   ```
3. ファイル名をテンプレートと一致させる

## JSON解析エラー

**エラー例**
```json
{
  "valid": false,
  "error": "JSON解析エラー: Expecting property name enclosed in double quotes"
}
```

**原因**: JSON形式のカラムでダブルクォートのエスケープが不適切

**修正方法**:
```csv
# 修正前（誤り）
id,json_data
1,{"key": "value"}

# 修正後（正しい）
id,json_data
1,"{""key"": ""value""}"
```

JSON内のダブルクォートを `""` でエスケープします。

## トラブルシューティング手順

### Step 1: エラーレポートを確認

統合検証の結果JSON（`validate_all.py`の出力）を確認します。

### Step 2: エラー種別を特定

`type` フィールドでエラー種別を確認：
- `column_order` → カラム順序不一致
- `missing_column` → カラム欠損
- `extra_column` → 余分なカラム
- `value_validation_error` → 値の検証エラー
- `invalid_header` → ヘッダー形式エラー

### Step 3: 該当箇所を修正

`row` と `column` フィールドで該当箇所を特定し、上記の修正方法を参照して修正します。

### Step 4: 再検証

修正後、再度検証スクリプトを実行してエラーが解消されたか確認します。

```bash
python .claude/skills/masterdata-validator/scripts/validate_all.py \
  --csv マスタデータ/運営仕様/<運営仕様名>/<ファイル名>.csv \
  --level full
```
