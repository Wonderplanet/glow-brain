# MySQL DB (mst/mng/admin) の操作例

MySQL接続のデータベース（mst, mng, admin）での具体的な操作例を示します。

## 目次

- [mst DB操作例](#mst-db操作例)
- [mng DB操作例](#mng-db操作例)
- [admin DB操作例](#admin-db操作例)
- [よくあるユースケース](#よくあるユースケース)

## mst DB操作例

mst DBには**マスターデータ（静的なゲームデータ）**と**オペレーションデータ**が格納されています。

### テーブル接頭辞

- `mst_*` - マスターデータ
- `opr_*` - オペレーションデータ（mst DBに含まれる）

### 例1: マスターユニット一覧の取得

**目的**: ゲームに登場するユニット（キャラクター）の一覧を確認

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label, rarity, element FROM mst_units LIMIT 20"
```

**結果例**:
```
id: unit_001
unit_label: Hero_Sword
rarity: SSR
element: Fire

id: unit_002
unit_label: Mage_Water
rarity: SR
element: Water
```

### 例2: 特定レアリティのユニットを検索

**目的**: SSRユニットだけを抽出

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label, rarity, element FROM mst_units WHERE rarity = 'SSR' ORDER BY id"
```

### 例3: マスターアイテム情報の確認

**目的**: アイテムマスターの構造とデータを確認

**ステップ1**: テーブル構造確認
```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_items"
```

**ステップ2**: データ取得
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, item_label, item_type FROM mst_items LIMIT 20"
```

### 例4: オペレーション設定の確認

**目的**: 運用設定（opr_configs）を確認

```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "opr_configs"
```

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT * FROM opr_configs LIMIT 10"
```

**重要**: `opr_*`テーブルも`database: "mst"`で接続します。

### 例5: ガチャマスターの排出設定確認

**目的**: ガチャの確率設定を確認

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT mst_gacha_id, mst_unit_id, weight FROM mst_gacha_weights WHERE mst_gacha_id = 'gacha_001' ORDER BY weight DESC"
```

### 例6: ステージマスターの取得

**目的**: ゲームステージ情報の確認

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, stage_number, difficulty, stamina_cost FROM mst_stages ORDER BY stage_number LIMIT 20"
```

### 例7: レアリティ別のユニット数集計

**目的**: レアリティごとに何体のユニットが存在するか確認

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT rarity, COUNT(*) as count FROM mst_units GROUP BY rarity ORDER BY rarity"
```

**結果例**:
```
rarity: N
count: 50

rarity: R
count: 30

rarity: SR
count: 20

rarity: SSR
count: 10
```

## mng DB操作例

mng DBには**運営データ（運営用の管理データ）**が格納されています。

### テーブル接頭辞

- `mng_*` - 運営データ

### 例8: 運営設定の確認

**目的**: 運営用の設定データを確認

```
mcp__glow-server-local-db__list_tables
database: "mng"
```

```
mcp__glow-server-local-db__query_database
database: "mng"
query: "SELECT * FROM mng_settings LIMIT 10"
```

### 例9: キャンペーン情報の取得

**目的**: 実施中のキャンペーン情報を確認

```
mcp__glow-server-local-db__query_database
database: "mng"
query: "SELECT id, campaign_name, start_at, end_at FROM mng_campaigns WHERE end_at >= NOW() ORDER BY start_at"
```

## admin DB操作例

admin DBには**管理画面データ**が格納されています。

### テーブル接頭辞

- `adm_*` - 管理画面データ

### 例10: 管理画面ユーザーの確認

**目的**: 管理画面にログインできるユーザーの一覧を確認

```
mcp__glow-server-local-db__describe_table
database: "admin"
table: "adm_users"
```

```
mcp__glow-server-local-db__query_database
database: "admin"
query: "SELECT id, name, email FROM adm_users LIMIT 10"
```

### 例11: 管理画面の権限設定確認

**目的**: 管理画面の権限ロール一覧を確認

```
mcp__glow-server-local-db__list_tables
database: "admin"
```

```
mcp__glow-server-local-db__query_database
database: "admin"
query: "SELECT * FROM adm_roles LIMIT 10"
```

## よくあるユースケース

### ユースケース1: 新ユニットのマスターデータ追加

**シナリオ**: 新しいユニットをマスターデータに追加

**ステップ1**: テーブル構造確認
```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_units"
```

**ステップ2**: 既存データの確認（IDの採番規則を理解）
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units ORDER BY id DESC LIMIT 5"
```

**ステップ3**: データ追加
```
mcp__glow-server-local-db__execute_query
database: "mst"
query: "INSERT INTO mst_units (id, unit_label, rarity, element, created_at, updated_at) VALUES ('unit_new_001', 'Dragon_Fire', 'SSR', 'Fire', NOW(), NOW())"
```

### ユースケース2: ガチャ確率の調整

**シナリオ**: 特定ユニットのガチャ排出確率を変更

**ステップ1**: 現在の設定を確認
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT * FROM mst_gacha_weights WHERE mst_gacha_id = 'gacha_001' AND mst_unit_id = 'unit_001'"
```

**ステップ2**: 確率（weight）を更新
```
mcp__glow-server-local-db__execute_query
database: "mst"
query: "UPDATE mst_gacha_weights SET weight = 50, updated_at = NOW() WHERE mst_gacha_id = 'gacha_001' AND mst_unit_id = 'unit_001'"
```

### ユースケース3: マスターデータのバージョン確認

**シナリオ**: 各マスターテーブルの最終更新日時を確認

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT MAX(updated_at) as last_updated FROM mst_units"
```

```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT MAX(updated_at) as last_updated FROM mst_items"
```

### ユースケース4: enum値の確認

**シナリオ**: マスターテーブルのenum型カラムに何が定義されているか確認

```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_units"
```

**結果から確認**:
```
Field: rarity
Type: enum('N','R','SR','SSR')
→ レアリティは N, R, SR, SSR の4種類

Field: element
Type: enum('Fire','Water','Wind','Earth')
→ 属性は Fire, Water, Wind, Earth の4種類
```

### ユースケース5: 特定条件のマスターデータ一括更新

**シナリオ**: 全てのFireユニットの攻撃力を10%増加

**ステップ1**: 対象データ確認
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label, element, attack FROM mst_units WHERE element = 'Fire'"
```

**ステップ2**: 一括更新
```
mcp__glow-server-local-db__execute_query
database: "mst"
query: "UPDATE mst_units SET attack = attack * 1.1, updated_at = NOW() WHERE element = 'Fire'"
```

### ユースケース6: マスターデータとユーザーデータの関連調査

**シナリオ**: 特定ユニットを何人のプレイヤーが所持しているか確認

**ステップ1**: マスターデータでユニットIDを確認
```
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units WHERE unit_label LIKE '%Dragon%'"
```

**ステップ2**: ユーザーデータで所持者数を確認（usrDB）
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT COUNT(DISTINCT usr_user_id) as owner_count FROM usr_units WHERE mst_unit_id = 'unit_001'"
```

**注意**: クロスDB JOINは使用できないため、個別にクエリを実行します。

## チェックリスト

MySQL DB操作時:
- [ ] テーブル接頭辞から正しいdatabaseを判定した
  - `mst_*`, `opr_*` → `database: "mst"`
  - `mng_*` → `database: "mng"`
  - `adm_*` → `database: "admin"`
- [ ] マスターデータ更新は慎重に行った
- [ ] enum型の値を事前に確認した
- [ ] UPDATE/DELETEにはWHERE句を必ず指定した
- [ ] クロスDB JOINは使用せず個別クエリで対応した

## 参考資料

- DB接続先判定: **[connection-guide.md](../connection-guide.md)**
- データ検索方法: **[guides/data-query.md](../guides/data-query.md)**
- データ更新方法: **[guides/data-modification.md](../guides/data-modification.md)**
