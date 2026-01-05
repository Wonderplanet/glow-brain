# データの追加・更新・削除方法

glow-server-local-db MCPを使用してデータを追加・更新・削除する方法を説明します。

## 目次

- [利用可能なMCPツール](#利用可能なmcpツール)
- [INSERT（データ追加）](#insertデータ追加)
- [UPDATE（データ更新）](#updateデータ更新)
- [DELETE（データ削除）](#deleteデータ削除)
- [トランザクション処理](#トランザクション処理)
- [注意事項](#注意事項)

## 利用可能なMCPツール

データ更新に使用するMCPツール:

### execute_query

INSERT, UPDATE, DELETE等の書き込みクエリを実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount) VALUES ('item_001', 'user_001', 'mat_001', 100)"
```

**重要**: データの追加・更新・削除には`execute_query`を使用してください。

## INSERT（データ追加）

### 基本構文

```sql
INSERT INTO table_name (column1, column2, column3)
VALUES (value1, value2, value3)
```

### 単一レコード追加

```sql
INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at)
VALUES ('item_new_001', 'user_001', 'mat_001', 100, NOW(), NOW())
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at) VALUES ('item_new_001', 'user_001', 'mat_001', 100, NOW(), NOW())"
```

### 複数レコード追加

```sql
INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at)
VALUES
    ('item_new_002', 'user_001', 'mat_002', 50, NOW(), NOW()),
    ('item_new_003', 'user_001', 'mat_003', 75, NOW(), NOW()),
    ('item_new_004', 'user_001', 'mat_004', 25, NOW(), NOW())
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at) VALUES ('item_new_002', 'user_001', 'mat_002', 50, NOW(), NOW()), ('item_new_003', 'user_001', 'mat_003', 75, NOW(), NOW()), ('item_new_004', 'user_001', 'mat_004', 25, NOW(), NOW())"
```

### デフォルト値を使う場合

```sql
INSERT INTO usr_users (id, status)
VALUES ('user_new_001', 'Active')
-- created_at, updated_at は自動設定される
```

### INSERT時の注意点

- **主キー（id）**: 必ず一意な値を指定
- **NOT NULLカラム**: 必ず値を指定
- **created_at/updated_at**: `NOW()`を使用するか、明示的にタイムスタンプを指定
- **文字列値**: シングルクォート(`'`)で囲む
- **数値**: クォート不要

## UPDATE（データ更新）

### 基本構文

```sql
UPDATE table_name
SET column1 = value1, column2 = value2
WHERE condition
```

**重要**: WHERE句は必須です。WHERE句がないと全レコードが更新されます。

### 単一カラム更新

```sql
UPDATE usr_items
SET amount = 200
WHERE id = 'item_001'
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_items SET amount = 200 WHERE id = 'item_001'"
```

### 複数カラム更新

```sql
UPDATE usr_items
SET amount = 150, updated_at = NOW()
WHERE id = 'item_001'
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_items SET amount = 150, updated_at = NOW() WHERE id = 'item_001'"
```

### 計算式を使った更新

```sql
UPDATE usr_items
SET amount = amount + 50, updated_at = NOW()
WHERE id = 'item_001'
```

### 複数レコード一括更新

```sql
UPDATE usr_units
SET level = 80, updated_at = NOW()
WHERE usr_user_id = 'user_001' AND rank >= 5
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_units SET level = 80, updated_at = NOW() WHERE usr_user_id = 'user_001' AND rank >= 5"
```

### 条件付き更新

```sql
UPDATE usr_user_parameters
SET level = level + 1, exp = exp - 1000, updated_at = NOW()
WHERE usr_user_id = 'user_001' AND exp >= 1000
```

### UPDATE時の注意点

- **WHERE句必須**: 必ず条件を指定（全件更新を避ける）
- **updated_at更新**: 更新時は`updated_at = NOW()`を含める
- **計算式**: `amount = amount + 10`のような相対的な更新が可能

## DELETE（データ削除）

### 基本構文

```sql
DELETE FROM table_name
WHERE condition
```

**重要**: WHERE句は必須です。WHERE句がないと全レコードが削除されます。

### 単一レコード削除

```sql
DELETE FROM usr_items
WHERE id = 'item_001'
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "DELETE FROM usr_items WHERE id = 'item_001'"
```

### 条件付き削除

```sql
DELETE FROM usr_items
WHERE usr_user_id = 'user_001' AND amount = 0
```

MCP実行:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "DELETE FROM usr_items WHERE usr_user_id = 'user_001' AND amount = 0"
```

### 複数レコード削除

```sql
DELETE FROM log_gacha_actions
WHERE created_at < '2024-01-01 00:00:00'
```

### DELETE時の注意点

- **WHERE句必須**: 必ず条件を指定（全件削除を避ける）
- **関連データ**: 外部キー制約がある場合は削除順序に注意
- **ログテーブル**: 基本的に削除しない（データ保持が重要）

## トランザクション処理

### 基本構文

```sql
START TRANSACTION;
-- 複数のクエリ
COMMIT;
-- またはエラー時
ROLLBACK;
```

### 実践例: アイテム移動

```sql
START TRANSACTION;

-- ユーザーAからアイテムを減らす
UPDATE usr_items
SET amount = amount - 10, updated_at = NOW()
WHERE usr_user_id = 'user_001' AND mst_item_id = 'mat_001';

-- ユーザーBにアイテムを増やす
UPDATE usr_items
SET amount = amount + 10, updated_at = NOW()
WHERE usr_user_id = 'user_002' AND mst_item_id = 'mat_001';

COMMIT;
```

**注意**: glow-server-local-db MCPでトランザクションを使用する場合、1つのexecute_queryで完結させる必要があります。

MCP実行（推奨しない）:
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "START TRANSACTION; UPDATE usr_items SET amount = amount - 10 WHERE usr_user_id = 'user_001' AND mst_item_id = 'mat_001'; UPDATE usr_items SET amount = amount + 10 WHERE usr_user_id = 'user_002' AND mst_item_id = 'mat_001'; COMMIT;"
```

**推奨**: 複雑なトランザクションはアプリケーション層（Laravel）で実装してください。

## 実践例

### 例1: テストユーザーにアイテムを追加

```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at) VALUES ('item_test_001', 'user_001', 'mat_gold', 9999, NOW(), NOW())"
```

### 例2: ユニットレベルを一括更新

```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_units SET level = 99, updated_at = NOW() WHERE usr_user_id = 'user_001'"
```

### 例3: 不要なログデータを削除

```
mcp__glow-server-local-db__execute_query
database: "log"
query: "DELETE FROM log_gacha_actions WHERE created_at < '2023-01-01 00:00:00' LIMIT 1000"
```

### 例4: ユーザーステータス更新

```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_users SET status = 'Banned', updated_at = NOW() WHERE id = 'user_bad_001'"
```

### 例5: アイテム数量調整

```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_items SET amount = amount + 500, updated_at = NOW() WHERE usr_user_id = 'user_001' AND mst_item_id = 'item_diamond'"
```

## 注意事項

### 開発環境でのみ実行

**重要**: データの追加・更新・削除は**開発環境（ローカル）でのみ実行**してください。

- ✅ ローカル開発環境: 実行OK
- ❌ 本番環境: 絶対に実行しない
- ❌ レビュー環境: 基本的に実行しない

### バックアップ

重要なデータを削除・更新する前に:
1. データをSELECTして確認
2. 必要に応じてバックアップを取得
3. WHERE句を慎重に確認

### WHERE句の検証

UPDATE/DELETE実行前に、WHERE句をSELECT文で検証:

```sql
-- 1. まずSELECTで確認
SELECT * FROM usr_items WHERE usr_user_id = 'user_001' AND amount = 0

-- 2. 対象が正しければUPDATE/DELETE実行
DELETE FROM usr_items WHERE usr_user_id = 'user_001' AND amount = 0
```

### タイムスタンプの扱い

- **INSERT時**: `created_at = NOW(), updated_at = NOW()`
- **UPDATE時**: `updated_at = NOW()`のみ更新
- **DELETE時**: タイムスタンプ不要

### データ整合性

- 外部キー制約を考慮
- 関連テーブルの整合性を保つ
- トランザクションが必要な場合はアプリケーション層で実装

## チェックリスト

データ更新前:
- [ ] 開発環境（ローカル）で実行することを確認した
- [ ] テーブル名から正しいdatabaseを判定した
- [ ] UPDATE/DELETEにはWHERE句を必ず指定した
- [ ] WHERE句をSELECTで事前検証した
- [ ] 必要に応じてデータをバックアップした
- [ ] タイムスタンプ（created_at/updated_at）を適切に設定した
- [ ] 外部キー制約や関連データを考慮した

## 参考資料

- DB接続先判定: **[connection-guide.md](../connection-guide.md)**
- テーブル構造確認: **[table-structure.md](table-structure.md)**
- データ検索: **[data-query.md](data-query.md)**
- 実装例: **[examples/mysql-operations.md](../examples/mysql-operations.md)**
