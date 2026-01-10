# テーブル構造の確認方法

glow-server-local-db MCPを使用してテーブル構造を確認する方法を説明します。

## 目次

- [利用可能なMCPツール](#利用可能なmcpツール)
- [テーブル一覧の取得](#テーブル一覧の取得)
- [テーブル構造の確認](#テーブル構造の確認)
- [インデックスの確認](#インデックスの確認)
- [実践例](#実践例)

## 利用可能なMCPツール

テーブル構造確認に使用するMCPツール:

### 1. list_databases

データベース一覧を取得:
```
mcp__glow-server-local-db__list_databases
```

### 2. list_tables

特定データベースのテーブル一覧を取得:
```
mcp__glow-server-local-db__list_tables
database: "usr" | "log" | "mst" | "mng" | "admin" | "sys"
```

### 3. describe_table

テーブル構造（カラム、型、制約）を取得:
```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_users"
```

### 4. show_indexes

テーブルのインデックス情報を取得:
```
mcp__glow-server-local-db__show_indexes
database: "usr"
table: "usr_users"
```

## テーブル一覧の取得

### 手順1: データベース一覧を確認

```
mcp__glow-server-local-db__list_databases
```

結果例:
```
databases:
  - mst
  - mng
  - admin
  - usr
  - log
  - sys
```

### 手順2: テーブル一覧を取得

特定のdatabaseのテーブル一覧を取得:

**ユーザーデータ (usr DB)**:
```
mcp__glow-server-local-db__list_tables
database: "usr"
```

結果例:
```
tables:
  - usr_users
  - usr_units
  - usr_items
  - usr_user_parameters
  - usr_gachas
  ...
```

**マスターデータ (mst DB)**:
```
mcp__glow-server-local-db__list_tables
database: "mst"
```

結果例:
```
tables:
  - mst_units
  - mst_items
  - mst_stages
  - opr_configs
  ...
```

## テーブル構造の確認

### 基本的な使い方

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_users"
```

### 結果の読み方

結果には以下の情報が含まれます:

```
Field: カラム名
Type: データ型 (varchar(255), int, timestamp等)
Null: NULL許可 (YES/NO)
Key: キー種別 (PRI=主キー, UNI=ユニーク, MUL=インデックス)
Default: デフォルト値
Extra: 追加情報 (auto_increment等)
```

### 実例: usr_usersテーブル

```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_users"
```

結果例:
```
Field: id
Type: varchar(255)
Null: NO
Key: PRI
Default: NULL
Extra:

Field: status
Type: varchar(255)
Null: NO
Key:
Default: Active
Extra:

Field: game_start_at
Type: timestamp
Null: YES
Key:
Default: NULL
Extra:

Field: created_at
Type: timestamp
Null: YES
Key:
Default: NULL
Extra:

Field: updated_at
Type: timestamp
Null: YES
Key:
Default: NULL
Extra:
```

### 実例: mst_unitsテーブル

```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_units"
```

結果例:
```
Field: id
Type: varchar(255)
Null: NO
Key: PRI
Default: NULL
Extra:

Field: unit_label
Type: varchar(255)
Null: NO
Key:
Default: NULL
Extra:

Field: rarity
Type: enum('N','R','SR','SSR')
Null: NO
Key:
Default: NULL
Extra:

Field: element
Type: enum('Fire','Water','Wind','Earth')
Null: NO
Key:
Default: NULL
Extra:

Field: created_at
Type: timestamp
Null: YES
Key:
Default: NULL
Extra:

Field: updated_at
Type: timestamp
Null: YES
Key:
Default: NULL
Extra:
```

## インデックスの確認

### 基本的な使い方

```
mcp__glow-server-local-db__show_indexes
database: "usr"
table: "usr_units"
```

### 結果の読み方

```
Table: テーブル名
Non_unique: ユニークインデックスか (0=ユニーク, 1=非ユニーク)
Key_name: インデックス名
Seq_in_index: インデックス内の順序
Column_name: カラム名
Collation: ソート順 (A=昇順, D=降順)
Cardinality: カーディナリティ（一意な値の数）
Index_type: インデックスタイプ (BTREE等)
```

### 実例: usr_unitsのインデックス

```
mcp__glow-server-local-db__show_indexes
database: "usr"
table: "usr_units"
```

結果例:
```
Key_name: PRIMARY
Column_name: id
Non_unique: 0
Index_type: BTREE

Key_name: usr_units_usr_user_id_mst_unit_id_unique
Column_name: usr_user_id
Non_unique: 0
Index_type: BTREE

Key_name: usr_units_usr_user_id_mst_unit_id_unique
Column_name: mst_unit_id
Non_unique: 0
Index_type: BTREE
```

## 実践例

### 例1: 新しいテーブルの構造を調査

**シナリオ**: `log_gacha_actions`テーブルの構造を知りたい

**手順**:

1. **database判定**
```
テーブル: log_gacha_actions
接頭辞: log_
database: "log"
```

2. **テーブル構造確認**
```
mcp__glow-server-local-db__describe_table
database: "log"
table: "log_gacha_actions"
```

3. **インデックス確認**
```
mcp__glow-server-local-db__show_indexes
database: "log"
table: "log_gacha_actions"
```

### 例2: データベース全体のテーブル調査

**シナリオ**: usr DBにどんなテーブルがあるか一覧で確認したい

**手順**:

1. **テーブル一覧取得**
```
mcp__glow-server-local-db__list_tables
database: "usr"
```

2. **気になるテーブルの構造確認**
```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_user_parameters"
```

### 例3: マスターテーブルの enum 値確認

**シナリオ**: mst_unitsのrarity（レアリティ）に何が定義されているか確認

**手順**:

1. **テーブル構造確認**
```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_units"
```

2. **結果からenumフィールドを確認**
```
Field: rarity
Type: enum('N','R','SR','SSR')
```

→ レアリティは N, R, SR, SSR の4種類

### 例4: 外部キー関連の調査

**シナリオ**: usr_unitsがどのマスターテーブルを参照しているか確認

**手順**:

1. **usr_units構造確認**
```
mcp__glow-server-local-db__describe_table
database: "usr"
table: "usr_units"
```

2. **外部キー候補のカラムを特定**
```
Field: mst_unit_id
Type: varchar(255)
→ mst_units.id を参照していると推測
```

3. **参照先テーブル確認**
```
mcp__glow-server-local-db__describe_table
database: "mst"
table: "mst_units"
```

## チェックリスト

テーブル構造確認時:
- [ ] テーブル名から正しいdatabaseを判定した
- [ ] describe_tableでカラム構造を確認した
- [ ] 主キー (PRI) を特定した
- [ ] NULL許可/禁止を確認した
- [ ] enumやデフォルト値を確認した
- [ ] 必要に応じてshow_indexesでインデックスを確認した
- [ ] 外部キー関連のカラムを特定した

## 参考資料

- DB接続先判定: **[connection-guide.md](../connection-guide.md)**
- マイグレーション規約: `.claude/skills/migration/common-rules.md`
