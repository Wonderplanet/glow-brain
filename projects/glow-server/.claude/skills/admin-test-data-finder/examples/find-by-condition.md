# 条件に応じたプレイヤー検索

様々なテスト観点に応じたプレイヤー検索の実例を示します。

## プレイヤー基本情報

### ケース1: アクティブなプレイヤーを探す

**テスト観点**: 通常のアクティブユーザーでのテスト

**クエリ**:

```sql
SELECT id, game_start_at, created_at
FROM usr_users
WHERE status = 0
ORDER BY created_at DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, game_start_at, created_at FROM usr_users WHERE status = 0 ORDER BY created_at DESC LIMIT 10"
```

### ケース2: 古参プレイヤーを探す

**テスト観点**: 長期プレイヤーのデータ確認

**クエリ**:

```sql
SELECT id, game_start_at, created_at
FROM usr_users
ORDER BY game_start_at ASC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, game_start_at, created_at FROM usr_users ORDER BY game_start_at ASC LIMIT 10"
```

### ケース3: 新規プレイヤーを探す

**テスト観点**: チュートリアル直後のデータ確認

**クエリ**:

```sql
SELECT id, game_start_at, tutorial_status, created_at
FROM usr_users
WHERE tutorial_status != ''
ORDER BY created_at DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, game_start_at, tutorial_status, created_at FROM usr_users WHERE tutorial_status != '' ORDER BY created_at DESC LIMIT 10"
```

## アイテム所持状況

### ケース4: 特定アイテム所持者を探す

**テスト観点**: アイテム付与・消費のテスト

**手順**:

1. **テーブル構造確認**

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_items"
```

2. **アイテム所持者を検索**

```sql
SELECT usr_user_id, mst_item_id, amount, created_at
FROM usr_items
WHERE amount > 0
ORDER BY amount DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_item_id, amount, created_at FROM usr_items WHERE amount > 0 ORDER BY amount DESC LIMIT 10"
```

### ケース5: 大量アイテム所持者

**テスト観点**: アイテム上限付近の動作確認

**クエリ**:

```sql
SELECT usr_user_id, mst_item_id, amount
FROM usr_items
WHERE amount >= 1000
ORDER BY amount DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_item_id, amount FROM usr_items WHERE amount >= 1000 ORDER BY amount DESC LIMIT 10"
```

### ケース6: 多種類アイテム所持者

**テスト観点**: アイテム一覧表示のテスト

**クエリ**:

```sql
SELECT usr_user_id, COUNT(*) as item_types
FROM usr_items
WHERE amount > 0
GROUP BY usr_user_id
HAVING item_types >= 10
ORDER BY item_types DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as item_types FROM usr_items WHERE amount > 0 GROUP BY usr_user_id HAVING item_types >= 10 ORDER BY item_types DESC LIMIT 10"
```

## 通貨所持状況

### ケース7: 有償通貨所持者

**テスト観点**: 課金通貨の表示・管理

**手順**:

1. **テーブル構造確認**

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_currency_paids"
```

2. **有償通貨所持者を検索**

```sql
SELECT usr_user_id, amount, bonus_amount, created_at
FROM usr_currency_paids
WHERE amount > 0
ORDER BY amount DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, amount, bonus_amount, created_at FROM usr_currency_paids WHERE amount > 0 ORDER BY amount DESC LIMIT 10"
```

### ケース8: 無償通貨所持者

**テスト観点**: 無償通貨の消費・付与

**クエリ**:

```sql
SELECT usr_user_id, amount, created_at
FROM usr_currency_frees
WHERE amount > 0
ORDER BY amount DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, amount, created_at FROM usr_currency_frees WHERE amount > 0 ORDER BY amount DESC LIMIT 10"
```

## ガチャ実行履歴

### ケース9: ガチャ実行者を探す

**テスト観点**: ガチャ履歴表示のテスト

**クエリ**:

```sql
SELECT usr_user_id, COUNT(*) as gacha_count
FROM log_gacha_actions
GROUP BY usr_user_id
HAVING gacha_count > 0
ORDER BY gacha_count DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, COUNT(*) as gacha_count FROM log_gacha_actions GROUP BY usr_user_id HAVING gacha_count > 0 ORDER BY gacha_count DESC LIMIT 10"
```

### ケース10: 最近ガチャを引いたプレイヤー

**テスト観点**: 最新のガチャ履歴確認

**クエリ**:

```sql
SELECT usr_user_id, created_at
FROM log_gacha_actions
ORDER BY created_at DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, created_at FROM log_gacha_actions ORDER BY created_at DESC LIMIT 10"
```

## ミッション進行状況

### ケース11: ミッション進行中のプレイヤー

**テスト観点**: ミッション表示・編集のテスト

**クエリ**:

```sql
SELECT usr_user_id, COUNT(*) as mission_count
FROM usr_mission_normals
GROUP BY usr_user_id
HAVING mission_count > 0
ORDER BY mission_count DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as mission_count FROM usr_mission_normals GROUP BY usr_user_id HAVING mission_count > 0 ORDER BY mission_count DESC LIMIT 10"
```

## パーティ編成

### ケース12: パーティ編成済みプレイヤー

**テスト観点**: パーティ表示・編集のテスト

**クエリ**:

```sql
SELECT usr_user_id, COUNT(*) as party_count
FROM usr_parties
GROUP BY usr_user_id
HAVING party_count > 0
ORDER BY party_count DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as party_count FROM usr_parties GROUP BY usr_user_id HAVING party_count > 0 ORDER BY party_count DESC LIMIT 10"
```

## 複合条件検索

### ケース13: 充実したデータを持つプレイヤー

**テスト観点**: 総合的なデータ確認

**要件**:
- ユニット複数所持
- アイテム複数所持
- ガチャ実行履歴あり

**手順**:

1. **ユニット所持者を絞り込み**

```sql
SELECT usr_user_id, COUNT(*) as unit_count
FROM usr_units
GROUP BY usr_user_id
HAVING unit_count >= 5
```

2. **結果から usr_user_id を取得し、アイテム所持を確認**

```sql
SELECT usr_user_id, COUNT(*) as item_types
FROM usr_items
WHERE usr_user_id IN ('user_001', 'user_002', 'user_003')
  AND amount > 0
GROUP BY usr_user_id
```

3. **ガチャ履歴も確認**

```sql
SELECT usr_user_id, COUNT(*) as gacha_count
FROM log_gacha_actions
WHERE usr_user_id IN ('user_001', 'user_002', 'user_003')
GROUP BY usr_user_id
```

### ケース14: プレイヤーレベルで絞り込み

**テスト観点**: 特定レベル帯のプレイヤー

**クエリ**:

```sql
SELECT usr_user_id, level, exp
FROM usr_user_parameters
WHERE level BETWEEN 10 AND 50
ORDER BY level DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, level, exp FROM usr_user_parameters WHERE level BETWEEN 10 AND 50 ORDER BY level DESC LIMIT 10"
```

### ケース15: ログインアクティブなプレイヤー

**テスト観点**: アクティブユーザーでのテスト

**クエリ**:

```sql
SELECT usr_user_id, MAX(created_at) as last_login
FROM log_logins
GROUP BY usr_user_id
ORDER BY last_login DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, MAX(created_at) as last_login FROM log_logins GROUP BY usr_user_id ORDER BY last_login DESC LIMIT 10"
```

## 実践例: EditUserUnit のテストデータ探索

**目的**: EditUserUnit ページのテストに最適なプレイヤーを探す

**要件**:
- 複数ユニット所持
- レベルが多様
- ランクも多様

**手順**:

1. **候補プレイヤーを検索**

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as unit_count, MIN(level) as min_level, MAX(level) as max_level, MIN(rank) as min_rank, MAX(rank) as max_rank FROM usr_units GROUP BY usr_user_id HAVING unit_count >= 5 ORDER BY unit_count DESC LIMIT 10"
```

2. **結果から適切な usr_user_id を取得** (例: user_005)

3. **そのユーザーのユニット詳細を確認**

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT mst_unit_id, level, rank, grade_level FROM usr_units WHERE usr_user_id = 'user_005'"
```

4. **admin画面で動作確認**

- 一覧: `http://localhost:8081/admin/user-unit?userId=user_005`
- 編集: `http://localhost:8081/admin/edit-user-unit?userId=user_005&unitId={mst_unit_id}`

## チェックリスト

条件別プレイヤー検索時:
- [ ] テスト観点を明確にした
- [ ] 必要なデータ条件を定義した
- [ ] 関連テーブルを特定した
- [ ] 適切なクエリを構築した
- [ ] 複数パターンのテストデータを確保した
- [ ] usr_user_id を取得した
- [ ] admin画面で実際のデータを確認した
