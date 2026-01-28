# 特定ユニット所持プレイヤーの検索

特定のユニット(キャラクター)を所持しているプレイヤーを検索する実例を示します。

## ユースケース

### ケース1: 特定ユニット所持者を探す

**テスト観点**: ユニットの詳細表示・編集機能のテスト

**手順**:

1. **テーブル構造確認**

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_units"
```

結果:
```
id: varchar(255) NOT NULL (PRI)
usr_user_id: varchar(255) NOT NULL (MUL)
mst_unit_id: varchar(255) NOT NULL
level: int unsigned NOT NULL DEFAULT 1
rank: int unsigned NOT NULL DEFAULT 1
grade_level: int unsigned NOT NULL DEFAULT 0
```

2. **ユニット所持者を検索**

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level, rank, grade_level, created_at FROM usr_units LIMIT 10"
limit: 10
```

結果例:
```
usr_user_id: user_001
mst_unit_id: unit_001
level: 25
rank: 3
grade_level: 2
```

3. **admin画面で確認**

取得した `usr_user_id` を使用:
- UserUnitページ: `http://localhost:8081/admin/user-unit?userId=user_001`
- EditUserUnitページ: `http://localhost:8081/admin/edit-user-unit?userId=user_001&unitId=unit_001`

### ケース2: 高レベルユニット所持者を探す

**テスト観点**: レベル上限付近の動作確認

**クエリ**:

```sql
SELECT usr_user_id, mst_unit_id, level, rank, grade_level
FROM usr_units
WHERE level >= 50
ORDER BY level DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level, rank, grade_level FROM usr_units WHERE level >= 50 ORDER BY level DESC LIMIT 10"
```

### ケース3: 特定ランク以上のユニット所持者

**テスト観点**: ランクアップ機能のテスト

**クエリ**:

```sql
SELECT usr_user_id, mst_unit_id, level, rank, grade_level
FROM usr_units
WHERE rank >= 5
ORDER BY rank DESC, level DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level, rank, grade_level FROM usr_units WHERE rank >= 5 ORDER BY rank DESC, level DESC LIMIT 10"
```

### ケース4: 複数ユニット所持者を探す

**テスト観点**: ユニット一覧表示のテスト (適度なデータ量)

**クエリ**:

```sql
SELECT usr_user_id, COUNT(*) as unit_count
FROM usr_units
GROUP BY usr_user_id
HAVING unit_count >= 5 AND unit_count <= 20
ORDER BY unit_count DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as unit_count FROM usr_units GROUP BY usr_user_id HAVING unit_count >= 5 AND unit_count <= 20 ORDER BY unit_count DESC LIMIT 10"
```

結果例:
```
usr_user_id: user_002
unit_count: 12
```

### ケース5: 新規取得ユニットがいるプレイヤー

**テスト観点**: 図鑑新規マークの表示確認

**クエリ**:

```sql
SELECT usr_user_id, mst_unit_id, is_new_encyclopedia, created_at
FROM usr_units
WHERE is_new_encyclopedia = 1
ORDER BY created_at DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, is_new_encyclopedia, created_at FROM usr_units WHERE is_new_encyclopedia = 1 ORDER BY created_at DESC LIMIT 10"
```

## 一括レベル更新のテストデータ

### ケース6: 一括レベル更新に適したプレイヤー

**テスト観点**: BulkUpdateUserUnitLevel機能のテスト

**要件**:
- 複数ユニット所持 (5体以上)
- レベルがバラバラ
- ランクも多様

**クエリ**:

```sql
SELECT
    usr_user_id,
    COUNT(*) as unit_count,
    MIN(level) as min_level,
    MAX(level) as max_level,
    AVG(level) as avg_level
FROM usr_units
GROUP BY usr_user_id
HAVING unit_count >= 5
ORDER BY unit_count DESC
LIMIT 10
```

MCP実行:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as unit_count, MIN(level) as min_level, MAX(level) as max_level, AVG(level) as avg_level FROM usr_units GROUP BY usr_user_id HAVING unit_count >= 5 ORDER BY unit_count DESC LIMIT 10"
```

結果例:
```
usr_user_id: user_003
unit_count: 15
min_level: 1
max_level: 80
avg_level: 35.5
```

この usr_user_id で一括レベル更新をテスト:
`http://localhost:8081/admin/bulk-update-user-unit-level?userId=user_003`

## ユニット詳細との連携

### ケース7: マスターデータとの関連確認

**クエリ**:

```sql
SELECT usr_user_id, mst_unit_id, level, rank, grade_level
FROM usr_units
WHERE mst_unit_id IN (
    SELECT id FROM mst.mst_units LIMIT 5
)
LIMIT 10
```

※ mst.mst_units は mst DB にあるため、別クエリで確認:

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units LIMIT 5"
```

取得した mst_unit_id で usr_units を検索:
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level, rank, grade_level FROM usr_units WHERE mst_unit_id = 'unit_001' LIMIT 10"
```

## チェックリスト

ユニット所持プレイヤー検索時:
- [ ] テスト対象のユニットID (mst_unit_id) を特定した
- [ ] 所持者が存在することを確認した
- [ ] レベル・ランクが適切な範囲のユーザーを選定した
- [ ] 複数パターン (高レベル/低レベル等) を確保した
- [ ] usr_user_id を取得した
- [ ] admin画面で実際のデータを確認した
