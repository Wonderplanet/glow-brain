# テストデータ探索のクエリ戦略

glow-server-local-db MCPを使用してテストデータを効率的に探索する戦略を説明します。

## 目次

- [基本戦略](#基本戦略)
- [DB構造理解](#db構造理解)
- [MCP使用方法](#mcp使用方法)
- [クエリパターン](#クエリパターン)

## 基本戦略

### テストデータ選定の原則

1. **実データの存在確認**: テスト対象のデータが実際に存在するユーザーを選ぶ
2. **適切なデータ量**: 多すぎず少なすぎない、テストに適した量
3. **多様なパターン**: 正常系・異常系の両方をカバー
4. **再現性**: 同じユーザーで繰り返しテスト可能

### 検索フロー

```
1. テスト観点の明確化
   ↓
2. 必要なデータ条件の定義
   ↓
3. 関連テーブルの特定
   ↓
4. クエリ実行
   ↓
5. usr_user_id取得
   ↓
6. admin画面で確認
```

## DB構造理解

### データベース分類

glow-serverは複数のデータベースに分かれています:

- **usr DB**: ユーザーデータ (usr_users, usr_units, usr_items等)
- **log DB**: ログデータ (log_units, log_gacha_actions等)
- **mst DB**: マスターデータ (mst_unit, mst_item等)

### 主要テーブル

**usr_users (ユーザーマスター)**
- `id`: usr_user_id (プライマリキー)
- `status`: ユーザーステータス
- `game_start_at`: ゲーム開始日時
- `created_at`: 作成日時

**usr_units (所持ユニット)**
- `usr_user_id`: ユーザーID
- `mst_unit_id`: ユニットID
- `level`: レベル
- `rank`: ランク
- `grade_level`: グレードレベル

**usr_items (所持アイテム)**
- `usr_user_id`: ユーザーID
- `mst_item_id`: アイテムID
- `amount`: 所持数

**usr_user_parameters (ユーザーパラメータ)**
- `usr_user_id`: ユーザーID
- `level`: プレイヤーレベル
- `exp`: 経験値

## MCP使用方法

### 利用可能なMCP関数

glow-server-local-db MCPが提供する関数:

#### 1. list_databases

データベース一覧取得:
```
mcp__glow-server-local-db__list_databases
```

#### 2. list_tables

テーブル一覧取得:
```
mcp__glow-server-local-db__list_tables
- database: "usr" | "log" | "mst" | "mng" | "admin" | "sys"
```

#### 3. describe_table

テーブル構造確認:
```
mcp__glow-server-local-db__describe_table
- database: "usr"
- table: "usr_units"
```

#### 4. query_database

SELECT クエリ実行:
```
mcp__glow-server-local-db__query_database
- database: "usr"
- query: "SELECT * FROM usr_users LIMIT 10"
- limit: 100 (デフォルト)
```

#### 5. execute_query

任意のSQLクエリ実行 (SELECT, INSERT, UPDATE等):
```
mcp__glow-server-local-db__execute_query
- database: "usr"
- query: "SELECT * FROM usr_users WHERE id = 'user_001'"
```

### クエリ実行の流れ

1. **テーブル構造確認**
```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_units"
```

2. **データ検索**
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level FROM usr_units WHERE mst_unit_id = 'unit_001' LIMIT 10"
```

3. **結果から usr_user_id を取得**

## クエリパターン

### パターン1: 基本検索

特定条件に一致するユーザーを探す:

```sql
SELECT usr_user_id
FROM usr_units
WHERE mst_unit_id = 'unit_001'
LIMIT 10
```

### パターン2: 集計を使った検索

データ量を確認しながら選定:

```sql
SELECT usr_user_id, COUNT(*) as unit_count
FROM usr_units
GROUP BY usr_user_id
HAVING unit_count > 10
LIMIT 10
```

### パターン3: JOIN検索

複数テーブルを組み合わせた条件:

```sql
SELECT u.id, u.game_start_at, COUNT(un.id) as unit_count
FROM usr_users u
LEFT JOIN usr_units un ON u.id = un.usr_user_id
GROUP BY u.id, u.game_start_at
HAVING unit_count > 5
LIMIT 10
```

### パターン4: 範囲検索

レベルや日時の範囲で絞り込み:

```sql
SELECT usr_user_id, level, rank
FROM usr_units
WHERE level BETWEEN 10 AND 50
  AND rank >= 3
LIMIT 10
```

### パターン5: 存在確認

特定データを持つユーザーを探す:

```sql
SELECT DISTINCT usr_user_id
FROM usr_items
WHERE mst_item_id = 'item_001'
  AND amount > 100
LIMIT 10
```

## テスト観点別の検索戦略

### ユニット関連テスト

**観点**: 特定ユニットの編集・表示

検索対象:
- `usr_units`: 所持ユニット
- `usr_user_parameters`: プレイヤー情報

戦略:
1. テスト対象ユニットIDを特定
2. そのユニットを所持するユーザーを検索
3. ユニット数が適度なユーザーを選定

### アイテム関連テスト

**観点**: アイテムの付与・消費

検索対象:
- `usr_items`: 所持アイテム
- `log_items`: アイテムログ

戦略:
1. テスト対象アイテムIDを特定
2. アイテムを所持するユーザーを検索
3. 十分な在庫があるユーザーを選定

### ミッション関連テスト

**観点**: ミッション進行状態

検索対象:
- `usr_mission_normals`: 通常ミッション
- `usr_mission_events`: イベントミッション

戦略:
1. 進行中のミッションがあるユーザーを検索
2. 達成済み・未達成の両パターンを確保

### ガチャ関連テスト

**観点**: ガチャ実行履歴

検索対象:
- `usr_gachas`: ガチャ状態
- `log_gacha_actions`: ガチャログ

戦略:
1. ガチャ実行履歴があるユーザーを検索
2. 実行回数が適度なユーザーを選定

## チェックリスト

テストデータ検索前:
- [ ] テスト観点を明確にした
- [ ] 必要なデータ条件を定義した
- [ ] 関連テーブルを特定した
- [ ] テーブル構造を describe_table で確認した

クエリ実行後:
- [ ] 適切な usr_user_id を取得した
- [ ] 複数パターンのテストデータを確保した
- [ ] admin画面で実際にデータを確認した
