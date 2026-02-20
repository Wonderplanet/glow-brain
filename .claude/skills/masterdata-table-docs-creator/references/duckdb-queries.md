# DuckDBクエリ集（テーブル調査用）

すべてのクエリは glow-brain リポジトリのルートディレクトリから実行する。

`{TableName}` を調査対象のテーブル名（PascalCase単数形）に置き換えて使用。

---

## Step 3-1: カラム構造確認

```bash
duckdb -c "
DESCRIBE SELECT *
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__');
"
```

---

## Step 3-2: サンプルデータ取得（上位10件）

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
LIMIT 10;
"
```

---

## Step 3-3: 総レコード数・有効レコード数確認

```bash
duckdb -c "
SELECT
  COUNT(*) AS total,
  COUNT(*) FILTER (WHERE ENABLE = 'e') AS enabled
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__');
"
```

---

## Step 3-4: 特定カラムの値一覧取得

enum値や外部キーの実際の値を確認する。

```bash
duckdb -c "
SELECT DISTINCT {column_name}, COUNT(*) AS cnt
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
GROUP BY {column_name}
ORDER BY cnt DESC;
"
```

---

## Step 3-5: 他テーブルとのJOINクエリパターン

### 外部キーで参照先テーブルとJOIN

```bash
duckdb -c "
SELECT
  t.*,
  ref.{ref_column} AS ref_name
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') t
LEFT JOIN read_csv('projects/glow-masterdata/{RefTableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__') ref
  ON t.{foreign_key_column} = ref.id
LIMIT 10;
"
```

### このテーブルを参照している他テーブルを検索

```bash
# MstAutoPlayerSequenceなどのaction_valueから参照されている場合
duckdb -c "
SELECT DISTINCT action_value
FROM read_csv('projects/glow-masterdata/MstAutoPlayerSequence.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE action_value IN (
  SELECT id
  FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
)
LIMIT 20;
"
```

---

## 補助クエリ: 特定IDのレコード取得

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE id = '{target_id}';
"
```

---

## 補助クエリ: NULL値の確認

```bash
duckdb -c "
SELECT
  COUNT(*) AS total,
  COUNT({column_name}) AS non_null,
  total - non_null AS null_count
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__');
"
```

---

## 補助クエリ: 条件フィルタリング

```bash
duckdb -c "
SELECT *
FROM read_csv('projects/glow-masterdata/{TableName}.csv', AUTO_DETECT=TRUE, nullstr='__NULL__')
WHERE {column_name} = '{value}'
ORDER BY id
LIMIT 20;
"
```

---

## DBスキーマ調査（jq）

```bash
# テーブル定義をjsonから取得（全カラム情報）
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.[] | select(.table_name == "{snake_table_name}")'

# テーブル名一覧（mst_プレフィックスのみ）
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '[.[].table_name] | unique | .[] | select(startswith("mst_"))'

# カラム名のみ抽出
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.[] | select(.table_name == "{snake_table_name}") | .column_name'

# enum値の確認（enum型カラムがある場合）
cat projects/glow-server/api/database/schema/exports/master_tables_schema.json \
  | jq '.[] | select(.table_name == "{snake_table_name}") | select(.enum_values != null) | {column_name, enum_values}'
```

---

## エラーパターンと対処

| エラー | 原因 | 対処 |
|-------|------|------|
| `No files found that match the pattern` | CSVファイルが存在しない or パスが誤り | glow-brainルートから実行しているか確認。CSVファイル名は `PascalCase単数形.csv` |
| `Table does not have a column named "X"` | カラム名の推測ミス | `DESCRIBE` クエリで実際のカラム名を確認してから再実行 |
| 結果が0行 | IDの指定ミス or データ未投入 | `LIMIT 10` で全件確認してから絞り込む |
| jqの結果が空 | snake_case変換ミス | `mst_items` のように末尾 `s` が必要か確認 |
| `nullstr` が効かない | DuckDBバージョンの差異 | `nullstr='__NULL__'` を `null_padding=true` に変更して試す |
