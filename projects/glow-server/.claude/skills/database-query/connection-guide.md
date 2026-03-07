# DB接続先の判定ルール

glow-serverは複数のデータベースに分かれています。テーブル名の接頭辞から正しいdatabase接続先を判定する方法を説明します。

## 目次

- [DB構成の概要](#db構成の概要)
- [テーブル接頭辞とdatabase対応表](#テーブル接頭辞とdatabase対応表)
- [判定ルール](#判定ルール)
- [よくある間違い](#よくある間違い)

## DB構成の概要

### データベース種別

glow-serverは以下のデータベースに分かれています:

**MySQL接続** (glow-server-local-db MCPの`database`パラメータ):
- **mst** - マスターデータ（静的なゲームデータ）
- **mng** - 運営データ（運営用の管理データ）
- **admin** - 管理画面データ

**TiDB接続** (glow-server-local-db MCPの`database`パラメータ):
- **usr** - ユーザーデータ（プレイヤー情報、所持アイテム等）
- **log** - ログデータ（ゲーム内アクション履歴）
- **sys** - システムデータ（システム設定、メンテナンス情報等）

### 接続設定の確認

データベース接続設定は以下のファイルで定義されています:
- `api/config/database.php:37-109`

## テーブル接頭辞とdatabase対応表

| テーブル接頭辞 | database | DB種別 | 用途 | 例 |
|------------|----------|--------|------|-----|
| `mst_` | `mst` | MySQL | マスターデータ | `mst_units`, `mst_items` |
| `opr_` | `mst` | MySQL | オペレーションデータ | `opr_configs` |
| `mng_` | `mng` | MySQL | 運営データ | `mng_settings` |
| `adm_` | `admin` | MySQL | 管理画面データ | `adm_users` |
| `usr_` | `usr` | TiDB | ユーザーデータ | `usr_users`, `usr_units` |
| `log_` | `log` | TiDB | ログデータ | `log_gacha_actions`, `log_units` |
| `sys_` | `sys` | TiDB | システムデータ | `sys_maintenance` |

## 判定ルール

### ステップ1: テーブル名から接頭辞を特定

テーブル名の最初のアンダースコアまでが接頭辞です:

✅ 正しい判定:
```
usr_users      → 接頭辞: usr_
mst_units      → 接頭辞: mst_
log_gacha_actions → 接頭辞: log_
```

### ステップ2: 接頭辞からdatabaseを判定

対応表を使ってdatabaseパラメータを決定:

```
usr_ → database: "usr"
mst_ → database: "mst"
opr_ → database: "mst"  ※ opr_もmstに含まれる
log_ → database: "log"
```

### ステップ3: MCPツール呼び出し

判定したdatabaseパラメータでMCPツールを呼び出し:

```
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_users LIMIT 10"
```

## 判定フローチャート

```
テーブル名を確認
    ↓
接頭辞を特定 (例: usr_users → usr_)
    ↓
対応表で database を判定
    ↓
  usr_ → database: "usr" (TiDB)
  log_ → database: "log" (TiDB)
  sys_ → database: "sys" (TiDB)
  mst_ → database: "mst" (MySQL)
  opr_ → database: "mst" (MySQL)
  mng_ → database: "mng" (MySQL)
  adm_ → database: "admin" (MySQL)
    ↓
MCPツール呼び出し
```

## よくある間違い

### ❌ 間違い1: databaseパラメータの誤指定

```
# ❌ usr_usersをmstで検索
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT * FROM usr_users LIMIT 10"

エラー: Table 'mst.usr_users' doesn't exist
```

✅ 正しい例:
```
# ✅ usr_usersをusrで検索
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_users LIMIT 10"
```

### ❌ 間違い2: opr_テーブルの接続先

```
# ❌ opr_configsをoprで検索
mcp__glow-server-local-db__query_database
database: "opr"
query: "SELECT * FROM opr_configs LIMIT 10"

エラー: database "opr" は存在しない
```

✅ 正しい例:
```
# ✅ opr_はmstに含まれる
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT * FROM opr_configs LIMIT 10"
```

### ❌ 間違い3: マルチDB結合クエリ

```
# ❌ 異なるDBのテーブルをJOIN
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT u.*, m.unit_label FROM usr_units u JOIN mst.mst_units m ON u.mst_unit_id = m.id LIMIT 10"

エラー: Cross-database JOIN is not allowed
```

✅ 正しい例:
```
# ✅ 個別にクエリを実行
# 1. mst_unitsから取得
mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units WHERE id = 'unit_001'"

# 2. 取得したmst_unit_idでusr_unitsを検索
mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT * FROM usr_units WHERE mst_unit_id = 'unit_001' LIMIT 10"
```

## 実践例

### 例1: ユーザー所持ユニットの確認

```
テーブル: usr_units
接頭辞: usr_
database: "usr" (TiDB)

mcp__glow-server-local-db__query_database
database: "usr"
query: "SELECT usr_user_id, mst_unit_id, level FROM usr_units LIMIT 10"
```

### 例2: マスターユニット情報の確認

```
テーブル: mst_units
接頭辞: mst_
database: "mst" (MySQL)

mcp__glow-server-local-db__query_database
database: "mst"
query: "SELECT id, unit_label FROM mst_units LIMIT 10"
```

### 例3: ガチャログの確認

```
テーブル: log_gacha_actions
接頭辞: log_
database: "log" (TiDB)

mcp__glow-server-local-db__query_database
database: "log"
query: "SELECT usr_user_id, mst_gacha_id, created_at FROM log_gacha_actions ORDER BY created_at DESC LIMIT 10"
```

### 例4: 管理画面ユーザーの確認

```
テーブル: adm_users
接頭辞: adm_
database: "admin" (MySQL)

mcp__glow-server-local-db__describe_table
database: "admin"
table: "adm_users"
```

## チェックリスト

DB操作前に必ず確認:
- [ ] テーブル名から接頭辞を特定した
- [ ] 対応表でdatabaseパラメータを確認した
- [ ] opr_テーブルは`mst`データベースであることを理解した
- [ ] クロスDB JOINは使用しない（個別クエリで対応）
- [ ] MCPツールのdatabaseパラメータを正しく指定した

## 参考資料

- データベース接続設定: `api/config/database.php:37-109`
- migrationスキル: `.claude/skills/migration/common-rules.md`
