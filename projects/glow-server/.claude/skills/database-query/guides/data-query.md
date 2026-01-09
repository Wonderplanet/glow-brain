# データの検索・確認方法

glow-server-local-db MCPを使用してデータを検索・確認する方法を説明します。

## 目次

- [利用可能なMCPツール](#利用可能なmcpツール)
- [基本的な検索パターン](#基本的な検索パターン)
- [条件指定の方法](#条件指定の方法)
- [集計・グループ化](#集計グループ化)
- [JOIN検索の代替方法](#join検索の代替方法)
- [実践例](#実践例)

## 利用可能なMCPツール

データ検索に使用するMCPツール:

### query_database

SELECT クエリを実行してデータを取得:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_users LIMIT 10"
limit: 100  # デフォルト100件、最大値の指定も可能
```

### execute_query

任意のSQLクエリを実行（SELECT, INSERT, UPDATE等）:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "SELECT * FROM usr_users WHERE id = 'user_001'"
```

**推奨**: データ検索には`query_database`を使用してください。`execute_query`はデータ更新時に使用します。

## 基本的な検索パターン

### パターン1: 全件取得（制限付き）

```sql
SELECT * FROM usr_users LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_users LIMIT 10"
```

### パターン2: 特定カラムのみ取得

```sql
SELECT id, status, game_start_at FROM usr_users LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, status, game_start_at FROM usr_users LIMIT 10"
```

### パターン3: 単一レコード取得

```sql
SELECT * FROM usr_users WHERE id = 'user_001'
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_users WHERE id = 'user_001'"
```

## 条件指定の方法

### WHERE句の基本

#### 完全一致

```sql
SELECT * FROM usr_units WHERE mst_unit_id = 'unit_001' LIMIT 10
```

#### 複数条件（AND）

```sql
SELECT * FROM usr_units
WHERE mst_unit_id = 'unit_001'
  AND level >= 50
LIMIT 10
```

#### 複数条件（OR）

```sql
SELECT * FROM usr_users
WHERE status = 'Active'
   OR status = 'Premium'
LIMIT 10
```

#### IN句（複数値指定）

```sql
SELECT * FROM usr_units
WHERE mst_unit_id IN ('unit_001', 'unit_002', 'unit_003')
LIMIT 10
```

### 範囲検索

#### BETWEEN

```sql
SELECT * FROM usr_units
WHERE level BETWEEN 10 AND 50
LIMIT 10
```

#### 比較演算子

```sql
SELECT * FROM usr_units
WHERE level >= 30
  AND rank > 3
LIMIT 10
```

### パターンマッチ

#### LIKE（部分一致）

```sql
SELECT * FROM usr_users
WHERE id LIKE 'user_%'
LIMIT 10
```

#### LIKE（前方一致）

```sql
SELECT * FROM usr_users
WHERE id LIKE 'user_001%'
LIMIT 10
```

### NULL判定

#### IS NULL

```sql
SELECT * FROM usr_users
WHERE game_start_at IS NULL
LIMIT 10
```

#### IS NOT NULL

```sql
SELECT * FROM usr_users
WHERE game_start_at IS NOT NULL
LIMIT 10
```

## ソート（ORDER BY）

### 昇順（ASC）

```sql
SELECT * FROM usr_units
ORDER BY level ASC
LIMIT 10
```

### 降順（DESC）

```sql
SELECT * FROM usr_units
ORDER BY level DESC
LIMIT 10
```

### 複数カラムでソート

```sql
SELECT * FROM usr_units
ORDER BY rank DESC, level DESC
LIMIT 10
```

### 日時でソート

```sql
SELECT * FROM log_gacha_actions
ORDER BY created_at DESC
LIMIT 10
```

## 集計・グループ化

### COUNT（件数）

```sql
SELECT COUNT(*) as total_count
FROM usr_units
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT COUNT(*) as total_count FROM usr_units"
```

### COUNT with WHERE

```sql
SELECT COUNT(*) as high_level_count
FROM usr_units
WHERE level >= 50
```

### GROUP BY（グループ集計）

```sql
SELECT usr_user_id, COUNT(*) as unit_count
FROM usr_units
GROUP BY usr_user_id
LIMIT 10
```

### HAVING（グループ条件）

```sql
SELECT usr_user_id, COUNT(*) as unit_count
FROM usr_units
GROUP BY usr_user_id
HAVING unit_count >= 5
ORDER BY unit_count DESC
LIMIT 10
```

### 集計関数

```sql
SELECT
    usr_user_id,
    COUNT(*) as unit_count,
    MIN(level) as min_level,
    MAX(level) as max_level,
    AVG(level) as avg_level
FROM usr_units
GROUP BY usr_user_id
LIMIT 10
```

## JOIN検索の代替方法

**重要**: glow-server-local-db MCPではクロスDB JOINは使用できません。個別クエリで対応してください。

### ❌ 使用できない例

```sql
-- ❌ 異なるDBのテーブルをJOIN
SELECT u.*, m.unit_label
FROM usr_units u
JOIN mst.mst_units m ON u.mst_unit_id = m.id
LIMIT 10
```

### ✅ 正しい方法（個別クエリ）

**手順1**: マスターデータから取得
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units WHERE id = 'unit_001'"
```

**手順2**: 取得したIDでユーザーデータを検索
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_units WHERE mst_unit_id = 'unit_001' LIMIT 10"
```

### 同一DB内のJOINは可能

```sql
SELECT u.id, u.status, p.level, p.exp
FROM usr_users u
JOIN usr_user_parameters p ON u.id = p.usr_user_id
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT u.id, u.status, p.level, p.exp FROM usr_users u JOIN usr_user_parameters p ON u.id = p.usr_user_id LIMIT 10"
```

## 実践例

### 例1: 特定ユーザーの所持ユニット一覧

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT mst_unit_id, level, rank, grade_level FROM usr_units WHERE usr_user_id = 'user_001' ORDER BY level DESC"
```

### 例2: 高レベルユニット所持者TOP10

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level, rank FROM usr_units WHERE level >= 80 ORDER BY level DESC LIMIT 10"
```

### 例3: ユニット所持数ランキング

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as unit_count FROM usr_units GROUP BY usr_user_id ORDER BY unit_count DESC LIMIT 10"
```

### 例4: 最近のガチャ実行ログ

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_gacha_id, created_at FROM log_gacha_actions ORDER BY created_at DESC LIMIT 20"
```

### 例5: 特定期間のログ検索

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_gacha_id, created_at FROM log_gacha_actions WHERE created_at >= '2025-01-01 00:00:00' ORDER BY created_at DESC LIMIT 50"
```

### 例6: マスターユニットのレアリティ別集計

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT rarity, COUNT(*) as count FROM mst_units GROUP BY rarity ORDER BY rarity"
```

### 例7: 特定アイテム所持ユーザー

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_item_id, amount FROM usr_items WHERE mst_item_id = 'item_001' AND amount > 100 LIMIT 10"
```

### 例8: ユーザーとパラメータの結合検索

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT u.id, u.status, p.level, p.exp, p.coin FROM usr_users u LEFT JOIN usr_user_parameters p ON u.id = p.usr_user_id LIMIT 10"
```

## パフォーマンス最適化

### LIMIT句の活用

大量のデータを扱う場合は必ずLIMIT句を使用:
```sql
SELECT * FROM log_gacha_actions ORDER BY created_at DESC LIMIT 100
```

### インデックスを活用したWHERE句

インデックスが張られているカラムで検索すると高速:
```sql
-- usr_user_id にはインデックスがある
SELECT * FROM usr_units WHERE usr_user_id = 'user_001'
```

### 必要なカラムのみ取得

`SELECT *`ではなく必要なカラムのみ指定:
```sql
-- ✅ 必要なカラムのみ
SELECT usr_user_id, mst_unit_id, level FROM usr_units LIMIT 10

-- ❌ 全カラム取得
SELECT * FROM usr_units LIMIT 10
```

## チェックリスト

データ検索時:
- [ ] テーブル名から正しいdatabaseを判定した
- [ ] SELECT文のWHERE句で適切な条件を指定した
- [ ] LIMIT句で件数を制限した
- [ ] 必要に応じてORDER BY句でソートした
- [ ] クロスDB JOINは使用せず個別クエリで対応した
- [ ] インデックスが張られているカラムで検索した
- [ ] 必要なカラムのみSELECTした

## 参考資料

- DB接続先判定: **[connection-guide.md](../connection-guide.md)**
- テーブル構造確認: **[table-structure.md](table-structure.md)**
- 実装例: **[examples/tidb-operations.md](../examples/tidb-operations.md)**
