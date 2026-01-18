# TiDB (usr/log/sys) の操作例

TiDB接続のデータベース（usr, log, sys）での具体的な操作例を示します。

## 目次

- [usr DB操作例](#usr-db操作例)
- [log DB操作例](#log-db操作例)
- [sys DB操作例](#sys-db操作例)
- [よくあるユースケース](#よくあるユースケース)
- [TiDB特有の注意事項](#tidb特有の注意事項)

## usr DB操作例

usr DBには**ユーザーデータ（プレイヤー情報、所持アイテム等）**が格納されています。

### テーブル接頭辞

- `usr_*` - ユーザーデータ

### 例1: ユーザー一覧の取得

**目的**: プレイヤー情報の確認

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, status, game_start_at, created_at FROM usr_users ORDER BY created_at DESC LIMIT 20"
```

**結果例**:
```
id: user_001
status: Active
game_start_at: 2025-01-15 10:30:00
created_at: 2025-01-15 10:25:00

id: user_002
status: Active
game_start_at: 2025-01-14 15:20:00
created_at: 2025-01-14 15:18:00
```

### 例2: 特定ユーザーの所持ユニット一覧

**目的**: プレイヤーが所持しているユニット（キャラクター）の一覧を確認

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, mst_unit_id, level, rank, grade_level, created_at FROM usr_units WHERE usr_user_id = 'user_001' ORDER BY level DESC"
```

**結果例**:
```
id: unit_instance_001
mst_unit_id: unit_001
level: 80
rank: 5
grade_level: 3
created_at: 2025-01-15 11:00:00
```

### 例3: 高レベルユニット所持者の検索

**目的**: レベル80以上のユニットを持つプレイヤーを検索

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT DISTINCT usr_user_id, mst_unit_id, level, rank FROM usr_units WHERE level >= 80 ORDER BY level DESC LIMIT 20"
```

### 例4: ユーザーパラメータの確認

**目的**: プレイヤーレベル、経験値、所持通貨を確認

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_user_parameters"
```

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, level, exp, coin, free_diamond, paid_diamond FROM usr_user_parameters WHERE usr_user_id = 'user_001'"
```

### 例5: アイテム所持状況の確認

**目的**: 特定ユーザーの所持アイテムを確認

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT mst_item_id, amount, created_at FROM usr_items WHERE usr_user_id = 'user_001' ORDER BY amount DESC LIMIT 20"
```

### 例6: ユニット所持数ランキング

**目的**: ユニット所持数が多いプレイヤーTOP10を取得

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, COUNT(*) as unit_count FROM usr_units GROUP BY usr_user_id ORDER BY unit_count DESC LIMIT 10"
```

**結果例**:
```
usr_user_id: user_003
unit_count: 45

usr_user_id: user_007
unit_count: 38

usr_user_id: user_001
unit_count: 32
```

### 例7: 特定アイテムを大量に所持しているユーザー

**目的**: アイテムを100個以上持っているプレイヤーを検索

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_item_id, amount FROM usr_items WHERE mst_item_id = 'item_gold' AND amount >= 100 ORDER BY amount DESC LIMIT 20"
```

### 例8: ガチャ状態の確認

**目的**: ユーザーのガチャ実行状態（天井カウント等）を確認

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_gacha_id, gacha_count, created_at FROM usr_gachas WHERE usr_user_id = 'user_001'"
```

### 例9: ミッション進行状況の確認

**目的**: ユーザーのミッション進行状況を確認

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT mst_mission_id, progress, is_completed, created_at FROM usr_mission_normals WHERE usr_user_id = 'user_001' ORDER BY is_completed, created_at DESC LIMIT 20"
```

### 例10: ユーザーとパラメータの結合検索

**目的**: ユーザー基本情報とパラメータを同時に取得

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT u.id, u.status, u.game_start_at, p.level, p.exp, p.coin FROM usr_users u LEFT JOIN usr_user_parameters p ON u.id = p.usr_user_id WHERE u.id = 'user_001'"
```

## log DB操作例

log DBには**ログデータ（ゲーム内アクション履歴）**が格納されています。

### テーブル接頭辞

- `log_*` - ログデータ

### 例11: ガチャ実行ログの確認

**目的**: 最近のガチャ実行履歴を確認

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_gacha_id, created_at FROM log_gacha_actions ORDER BY created_at DESC LIMIT 30"
```

**結果例**:
```
usr_user_id: user_005
mst_gacha_id: gacha_001
created_at: 2025-01-20 14:30:15

usr_user_id: user_012
mst_gacha_id: gacha_002
created_at: 2025-01-20 14:25:42
```

### 例12: ユニット取得ログの確認

**目的**: 新規ユニット取得のログを確認

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_unit_id, content_type, target_id, created_at FROM log_units ORDER BY created_at DESC LIMIT 30"
```

### 例13: 特定ユーザーのガチャログ

**目的**: 特定ユーザーのガチャ実行履歴を時系列で確認

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT mst_gacha_id, created_at FROM log_gacha_actions WHERE usr_user_id = 'user_001' ORDER BY created_at DESC LIMIT 50"
```

### 例14: アイテム使用ログの確認

**目的**: アイテム使用・取得のログを確認

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_item_id, amount, content_type, created_at FROM log_items ORDER BY created_at DESC LIMIT 30"
```

### 例15: ログ件数の集計

**目的**: 日別のガチャ実行回数を集計

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT DATE(created_at) as date, COUNT(*) as count FROM log_gacha_actions WHERE created_at >= '2025-01-01' GROUP BY DATE(created_at) ORDER BY date DESC"
```

**結果例**:
```
date: 2025-01-20
count: 145

date: 2025-01-19
count: 132

date: 2025-01-18
count: 128
```

### 例16: 特定期間のログ検索

**目的**: 2025年1月のガチャログを検索

```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_gacha_id, created_at FROM log_gacha_actions WHERE created_at >= '2025-01-01 00:00:00' AND created_at < '2025-02-01 00:00:00' ORDER BY created_at DESC LIMIT 100"
```

## sys DB操作例

sys DBには**システムデータ（システム設定、メンテナンス情報等）**が格納されています。

### テーブル接頭辞

- `sys_*` - システムデータ

### 例17: システムテーブル一覧の確認

```
mcp__glow-server-local-db__list_tables
database: "sys"
```

### 例18: メンテナンス情報の確認

**目的**: メンテナンススケジュールを確認

```
mcp__glow-server-local-db__query_database
database: "sys"
query: "SELECT * FROM sys_maintenance ORDER BY start_at DESC LIMIT 10"
```

### 例19: システム設定の確認

```
mcp__glow-server-local-db__query_database
database: "sys"
query: "SELECT * FROM sys_configs LIMIT 20"
```

## よくあるユースケース

### ユースケース1: テストユーザーにアイテムを付与

**シナリオ**: テストユーザーに大量のゴールドを付与

**ステップ1**: 現在の所持状況を確認
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_items WHERE usr_user_id = 'user_001' AND mst_item_id = 'item_gold'"
```

**ステップ2**: アイテムが存在する場合は更新
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_items SET amount = amount + 10000, updated_at = NOW() WHERE usr_user_id = 'user_001' AND mst_item_id = 'item_gold'"
```

**ステップ3**: アイテムが存在しない場合は新規追加
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_items (id, usr_user_id, mst_item_id, amount, created_at, updated_at) VALUES ('item_test_001', 'user_001', 'item_gold', 10000, NOW(), NOW())"
```

### ユースケース2: ユニットレベルを一括更新

**シナリオ**: テストユーザーの全ユニットをレベル99に設定

**ステップ1**: 対象ユニットを確認
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, mst_unit_id, level FROM usr_units WHERE usr_user_id = 'user_001'"
```

**ステップ2**: レベル一括更新
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_units SET level = 99, updated_at = NOW() WHERE usr_user_id = 'user_001'"
```

### ユースケース3: ガチャ実行履歴の分析

**シナリオ**: 特定ガチャの実行回数と実行ユーザー数を集計

**ステップ1**: 実行回数
```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT COUNT(*) as total_count FROM log_gacha_actions WHERE mst_gacha_id = 'gacha_001'"
```

**ステップ2**: ユニークユーザー数
```
mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT COUNT(DISTINCT usr_user_id) as unique_users FROM log_gacha_actions WHERE mst_gacha_id = 'gacha_001'"
```

### ユースケース4: ユーザーデータのリセット

**シナリオ**: テストユーザーの所持ユニットを全削除

**ステップ1**: 削除対象を確認
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT COUNT(*) as count FROM usr_units WHERE usr_user_id = 'user_test_001'"
```

**ステップ2**: 削除実行
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "DELETE FROM usr_units WHERE usr_user_id = 'user_test_001'"
```

### ユースケース5: 新規ユーザー作成

**シナリオ**: テスト用の新規ユーザーを作成

**ステップ1**: usr_usersにユーザーを追加
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_users (id, status, game_start_at, created_at, updated_at) VALUES ('user_test_new_001', 'Active', NOW(), NOW(), NOW())"
```

**ステップ2**: usr_user_parametersに初期パラメータを追加
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "INSERT INTO usr_user_parameters (id, usr_user_id, level, exp, coin, free_diamond, paid_diamond, created_at, updated_at) VALUES ('param_test_new_001', 'user_test_new_001', 1, 0, 1000, 100, 0, NOW(), NOW())"
```

### ユースケース6: ユーザーステータス変更

**シナリオ**: ユーザーを一時停止状態にする

**ステップ1**: 現在のステータス確認
```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT id, status FROM usr_users WHERE id = 'user_001'"
```

**ステップ2**: ステータス更新
```
mcp__glow-server-local-db__execute_query
database: "usr"
query: "UPDATE usr_users SET status = 'Suspended', updated_at = NOW() WHERE id = 'user_001'"
```

### ユースケース7: 大量データの集計

**シナリオ**: 全ユーザーの平均レベルを算出

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT AVG(level) as avg_level, MIN(level) as min_level, MAX(level) as max_level FROM usr_user_parameters"
```

## TiDB特有の注意事項

### 大量データへの配慮

TiDBには**大量のユーザーデータやログデータ**が格納されています:

- **LIMIT句を必ず使用**: 全件取得は避ける
- **インデックスを活用**: WHERE句でインデックスカラムを指定
- **集計は慎重に**: COUNT(*)やAVG()は大量データで時間がかかる可能性

### enum型は使用禁止

TiDBテーブルでは**enum型を使用してはいけません**:

- 理由: 大量レコードを持つテーブルでALTER TABLEは困難
- 代替: varchar(255)を使用し、コメントで値を明記

**例**:
```
Field: status
Type: varchar(255)
Default: Active
Comment: ステータス: Active, Inactive, Suspended
```

### パフォーマンス最適化

- **適切なLIMIT**: `LIMIT 100`など適切な件数制限
- **インデックス確認**: `show_indexes`でインデックスを確認
- **WHERE句最適化**: インデックスが張られているカラムで検索

```
mcp__glow-server-local-db__show_indexes
database: "usr"
table: "usr_units"
```

## チェックリスト

TiDB操作時:
- [ ] テーブル接頭辞から正しいdatabaseを判定した
  - `usr_*` → `database: "usr"`
  - `log_*` → `database: "log"`
  - `sys_*` → `database: "sys"`
- [ ] LIMIT句で件数を制限した
- [ ] enum型は使用しない（varchar使用）
- [ ] 大量データの集計は慎重に実行
- [ ] インデックスを活用したWHERE句を使用
- [ ] UPDATE/DELETEにはWHERE句を必ず指定

## 参考資料

- DB接続先判定: **[connection-guide.md](../connection-guide.md)**
- データ検索方法: **[guides/data-query.md](../guides/data-query.md)**
- データ更新方法: **[guides/data-modification.md](../guides/data-modification.md)**
- マイグレーション規約: `.claude/skills/migration/common-rules.md`
