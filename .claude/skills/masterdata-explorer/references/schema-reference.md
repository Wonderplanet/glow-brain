# DBスキーマ参照ガイド

このドキュメントでは、DBスキーマJSON（`master_tables_schema.json`）の詳細な調査方法を説明します。

## 目次

1. [DBスキーマJSON構造](#dbスキーマjson構造)
2. [jqコマンドパターン集](#jqコマンドパターン集)
3. [列情報の読み方](#列情報の読み方)
4. [実践例](#実践例)

## DBスキーマJSON構造

### ファイルパス

```
projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**サイズ**: 471KB

### 全体構造

```json
{
  "databases": {
    "mst": {
      "tables": {
        "mst_units": {
          "columns": {
            "id": { ... },
            "rarity": { ... },
            ...
          },
          "indexes": [ ... ],
          "comment": "..."
        },
        ...
      }
    }
  }
}
```

### 階層構造

```
.databases
  └─ .mst（データベース名）
      └─ .tables（テーブル群）
          ├─ .mst_units（テーブル名）
          │   ├─ .columns（カラム定義）
          │   │   ├─ .id（カラム名）
          │   │   │   ├─ .type（データ型）
          │   │   │   ├─ .nullable（NULL許可）
          │   │   │   ├─ .default（デフォルト値）
          │   │   │   ├─ .enum（enum値配列）
          │   │   │   ├─ .foreign_key（外部キー参照先）
          │   │   │   ├─ .comment（コメント）
          │   │   │   └─ ...
          │   │   ├─ .rarity
          │   │   └─ ...
          │   ├─ .indexes（インデックス定義）
          │   └─ .comment（テーブルコメント）
          ├─ .mst_stages
          └─ ...
```

### カラム定義の構造

各カラムは以下の情報を持ちます：

| フィールド | 型 | 説明 | 例 |
|---------|-----|------|-----|
| `type` | string | データ型 | `"int"`, `"varchar(255)"`, `"text"` |
| `nullable` | boolean | NULL許可 | `true`, `false` |
| `default` | any | デフォルト値 | `null`, `0`, `""`, `"active"` |
| `enum` | array | enum値の配列 | `["fire", "water", "earth"]` |
| `foreign_key` | string | 外部キー参照先 | `"mst_rarities.id"` |
| `comment` | string | カラムコメント | `"キャラクターの名前"` |
| `auto_increment` | boolean | 自動増分 | `true`, `false` |
| `primary_key` | boolean | 主キー | `true`, `false` |

## jqコマンドパターン集

### 基本パターン

#### 1. 全テーブル名を取得

```bash
jq '.databases.mst.tables | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  "mst_units",
  "mst_stages",
  "opr_events",
  ...
]
```

#### 2. テーブル数を取得

```bash
jq '.databases.mst.tables | keys | length' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力**: `165`

#### 3. 特定のテーブル構造を全て取得

```bash
jq '.databases.mst.tables.mst_units' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 4. 特定のテーブルの全カラム名を取得

```bash
jq '.databases.mst.tables.mst_units.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  "id",
  "rarity",
  "max_hp",
  "min_hp",
  "max_attack_power",
  "min_attack_power",
  ...
]
```

#### 5. 特定のカラムの詳細情報を取得

```bash
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
{
  "type": "enum",
  "nullable": false,
  "default": "normal",
  "enum": ["normal", "step_up", "limited", "pickup"],
  "comment": "ガチャのタイプ"
}
```

### 検索パターン

#### 6. テーブル名の部分一致検索

```bash
# 「event」を含むテーブル名を検索
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  "mst_event_boss",
  "mst_event_exchange",
  "opr_events",
  "opr_event_banners",
  ...
]
```

#### 7. Mst*テーブルのみ抽出

```bash
jq '.databases.mst.tables | keys | map(select(test("^mst_")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 8. Opr*テーブルのみ抽出

```bash
jq '.databases.mst.tables | keys | map(select(test("^opr_")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 9. I18nテーブルのみ抽出

```bash
jq '.databases.mst.tables | keys | map(select(test("_i18n$")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### カラム情報取得パターン

#### 10. NULL許可カラムの一覧を取得

```bash
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.nullable == true)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  "mst_artwork_fragment_drop_group_id",
  "prev_mst_stage_id",
  "next_mst_stage_id"
]
```

#### 11. NOT NULLカラムの一覧を取得

```bash
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.nullable == false)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 12. デフォルト値を持つカラムの一覧

```bash
jq '.databases.mst.tables.opr_gachas.columns |
  to_entries |
  map(select(.value.default != null)) |
  map({column: .key, default: .value.default})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {"column": "is_enabled", "default": true},
  {"column": "gacha_type", "default": "normal"},
  ...
]
```

#### 13. enum型カラムの一覧とenum値

```bash
jq '.databases.mst.tables.opr_gachas.columns |
  to_entries |
  map(select(.value.enum != null)) |
  map({column: .key, enum: .value.enum})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {
    "column": "gacha_type",
    "enum": ["normal", "step_up", "limited", "pickup"]
  }
]
```

#### 14. 外部キーを持つカラムの一覧

```bash
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {"column": "mst_quest_id", "references": "mst_quests.id"},
  {"column": "drop_group_id", "references": "mst_drop_groups.id"},
  {"column": "first_clear_reward_group_id", "references": "mst_reward_groups.id"}
]
```

#### 15. 主キーカラムの取得

```bash
jq '.databases.mst.tables.mst_units.columns |
  to_entries |
  map(select(.value.primary_key == true)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力**: `["id"]`

#### 16. 自動増分カラムの取得

```bash
jq '.databases.mst.tables.mst_units.columns |
  to_entries |
  map(select(.value.auto_increment == true)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 横断検索パターン

#### 17. 特定のテーブルを参照している全テーブルを検索

```bash
# MstRewardGroupを参照している全テーブルを検索
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    columns: (
      .value.columns |
      to_entries |
      map(select(.value.foreign_key != null and (.value.foreign_key | test("mst_reward_groups")))) |
      map(.key)
    )
  }) |
  select(.columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {
    "table": "mst_stages",
    "columns": ["first_clear_reward_group_id"]
  },
  {
    "table": "opr_event_points",
    "columns": ["reward_group_id"]
  },
  ...
]
```

#### 18. 特定のカラム名を持つ全テーブルを検索

```bash
# 「reward_group_id」というカラムを持つ全テーブルを検索
jq '.databases.mst.tables |
  to_entries |
  map(select(.value.columns | has("reward_group_id"))) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  "mst_missions",
  "opr_missions",
  "opr_event_points",
  "mst_advent_battle_rewards",
  ...
]
```

#### 19. 全テーブルのenum型カラムを一覧表示

```bash
jq '.databases.mst.tables |
  to_entries[] |
  {
    table: .key,
    enums: (
      .value.columns |
      to_entries |
      map(select(.value.enum != null)) |
      map({column: .key, values: .value.enum})
    )
  } |
  select(.enums | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 20. 特定のデータ型を持つカラムを全テーブルから検索

```bash
# すべての「text」型カラムを検索
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    text_columns: (
      .value.columns |
      to_entries |
      map(select(.value.type == "text")) |
      map(.key)
    )
  }) |
  select(.text_columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### フィルタリング・集計パターン

#### 21. カラム数の多いテーブルTOP10

```bash
jq '.databases.mst.tables |
  to_entries |
  map({table: .key, column_count: (.value.columns | keys | length)}) |
  sort_by(.column_count) |
  reverse |
  .[0:10]' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {"table": "mst_units", "column_count": 30},
  {"table": "mst_stages", "column_count": 35},
  ...
]
```

#### 22. 外部キーの多いテーブルTOP10

```bash
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    fk_count: (
      .value.columns |
      to_entries |
      map(select(.value.foreign_key != null)) |
      length
    )
  }) |
  sort_by(.fk_count) |
  reverse |
  .[0:10]' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 23. NULL許可カラムの割合が高いテーブルTOP10

```bash
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    total: (.value.columns | keys | length),
    nullable: (
      .value.columns |
      to_entries |
      map(select(.value.nullable == true)) |
      length
    )
  }) |
  map({
    table: .table,
    nullable_ratio: (.nullable / .total * 100 | floor)
  }) |
  sort_by(.nullable_ratio) |
  reverse |
  .[0:10]' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### テーブルコメント・カラムコメント取得

#### 24. テーブルコメントの取得

```bash
jq '.databases.mst.tables.mst_units.comment' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 25. カラムコメント一覧の取得

```bash
jq '.databases.mst.tables.mst_units.columns |
  to_entries |
  map({column: .key, comment: .value.comment})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### 26. コメントが未設定のカラムを検索

```bash
jq '.databases.mst.tables.mst_units.columns |
  to_entries |
  map(select(.value.comment == null or .value.comment == "")) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### インデックス情報取得

#### 27. テーブルのインデックス一覧を取得

```bash
jq '.databases.mst.tables.mst_units.indexes' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```json
[
  {
    "name": "idx_mst_units_rarity",
    "columns": ["rarity"],
    "unique": false
  },
  {
    "name": "idx_mst_units_series_id",
    "columns": ["mst_series_id"],
    "unique": false
  }
]
```

#### 28. 複合インデックスを持つテーブルを検索

```bash
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    composite_indexes: (
      .value.indexes |
      map(select(.columns | length > 1))
    )
  }) |
  select(.composite_indexes | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 型変換・整形パターン

#### 29. カラム定義をCSV形式で出力

```bash
jq -r '.databases.mst.tables.mst_units.columns |
  to_entries |
  map([.key, .value.type, .value.nullable, .value.default // "NULL"] | @csv) |
  .[]' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```
"id","bigint",false,"NULL"
"rarity","int",false,"NULL"
"max_hp","int",false,"NULL"
```

#### 30. マークダウンテーブル形式で出力

```bash
jq -r '.databases.mst.tables.mst_units.columns |
  ["| カラム名 | 型 | NULL許可 | デフォルト値 |", "| --- | --- | --- | --- |"] +
  (to_entries | map("| \(.key) | \(.value.type) | \(.value.nullable) | \(.value.default // "NULL") |")) |
  .[]' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**:
```markdown
| カラム名 | 型 | NULL許可 | デフォルト値 |
| --- | --- | --- | --- |
| id | bigint | false | NULL |
| rarity | int | false | NULL |
| max_hp | int | false | NULL |
```

## 列情報の読み方

### データ型（type）

#### 数値型

| 型 | 範囲 | 用途例 |
|-----|------|--------|
| `tinyint` | -128 〜 127 | フラグ、小さなID |
| `int` | -2,147,483,648 〜 2,147,483,647 | 一般的なID、数値 |
| `bigint` | -9,223,372,036,854,775,808 〜 9,223,372,036,854,775,807 | 大きなID、ユーザーID |
| `decimal(10,2)` | 固定小数点 | 金額、レート |
| `float` | 浮動小数点 | 倍率、確率 |

#### 文字列型

| 型 | 最大長 | 用途例 |
|-----|--------|--------|
| `varchar(255)` | 255文字 | 名前、短いテキスト |
| `varchar(1000)` | 1000文字 | 説明文 |
| `text` | 65,535文字 | 長文、JSONデータ |
| `longtext` | 4GB | 非常に長い文章 |

#### 日時型

| 型 | 形式 | 用途例 |
|-----|------|--------|
| `date` | YYYY-MM-DD | 日付のみ |
| `datetime` | YYYY-MM-DD HH:MM:SS | 日時 |
| `timestamp` | YYYY-MM-DD HH:MM:SS | 作成日時、更新日時 |

#### その他

| 型 | 説明 | 用途例 |
|-----|------|--------|
| `enum` | 列挙型 | ステータス、タイプ |
| `boolean` | 真偽値 | フラグ |
| `json` | JSON形式 | 複雑な構造データ |

### NULL許可（nullable）

- `true`: NULL値を許可（データが存在しない可能性がある）
- `false`: NULL値を許可しない（必須項目）

**注意点**:
- `nullable: false`のカラムは、CSVで`__NULL__`を指定できません
- CSVでデータを作成する際は、必ず値を指定する必要があります

### デフォルト値（default）

データ挿入時に値が指定されなかった場合に使用される値：

- `null`: NULL値
- `0`: 数値の0
- `""`: 空文字列
- `true`/`false`: 真偽値
- `"active"`: 文字列値

### enum型（enum）

取りうる値が限定されている場合に使用：

```json
{
  "type": "enum",
  "enum": ["fire", "water", "earth", "wind", "light", "dark"],
  "nullable": false,
  "default": "fire"
}
```

**重要**: CSVでは、`enum`配列に含まれる値のみが有効です。それ以外の値を指定するとバリデーションエラーになります。

### 外部キー（foreign_key）

他のテーブルを参照している場合に設定：

```json
{
  "type": "int",
  "nullable": false,
  "foreign_key": "mst_rarities.id"
}
```

**形式**: `"テーブル名.カラム名"`

**意味**: このカラムの値は、`mst_rarities`テーブルの`id`カラムに存在する値でなければならない

**注意点**:
- 外部キー制約がある場合、参照先のデータが存在しない値を指定するとエラーになります
- CSV作成時は、参照先のテーブルを先に作成する必要があります

### コメント（comment）

カラムの説明文：

```json
{
  "type": "varchar(255)",
  "comment": "キャラクターの名前（表示用ではなく内部管理用）"
}
```

**用途**:
- カラムの役割を理解する
- データの用途を確認する
- 制約や注意事項を確認する

## 実践例

### 例1: 新しいテーブルの構造を理解する

**シナリオ**: `MstAdventBattle`テーブルについて調べたい

```bash
# 1. テーブル全体の構造を確認
jq '.databases.mst.tables.mst_advent_battles' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. カラム名の一覧を確認
jq '.databases.mst.tables.mst_advent_battles.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. 外部キーを持つカラムを確認（他のテーブルとの関連を理解）
jq '.databases.mst.tables.mst_advent_battles.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 4. enum型カラムを確認（取りうる値を理解）
jq '.databases.mst.tables.mst_advent_battles.columns |
  to_entries |
  map(select(.value.enum != null)) |
  map({column: .key, enum: .value.enum})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 5. NULL許可カラムを確認（どのカラムが省略可能か理解）
jq '.databases.mst.tables.mst_advent_battles.columns |
  to_entries |
  map(select(.value.nullable == true)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例2: ガチャシステムの全体像を把握する

**シナリオ**: ガチャ関連のテーブルとその関連を調べたい

```bash
# 1. ガチャ関連のテーブルを全て抽出
jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. OprGachaの構造を確認
jq '.databases.mst.tables.opr_gachas' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. OprGachaを参照している全テーブルを逆引き
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    columns: (
      .value.columns |
      to_entries |
      map(select(.value.foreign_key != null and (.value.foreign_key | test("opr_gachas")))) |
      map(.key)
    )
  }) |
  select(.columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 4. OprGachaのenum型カラムを確認（ガチャタイプなど）
jq '.databases.mst.tables.opr_gachas.columns |
  to_entries |
  map(select(.value.enum != null)) |
  map({column: .key, enum: .value.enum})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例3: イベントポイント報酬の仕組みを理解する

**シナリオ**: イベント表示報酬がどのように設定されているか調べたい

```bash
# 1. mst_event_display_rewardsの構造を確認
jq '.databases.mst.tables.mst_event_display_rewards' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. 外部キーを確認（どのテーブルと関連しているか）
jq '.databases.mst.tables.mst_event_display_rewards.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
# 出力: mst_event_id → mst_events.id, mst_reward_group_id → mst_reward_groups.id
#       reward_group_id → mst_reward_groups.id

# 3. MstRewardGroupの構造を確認（報酬がどう定義されているか）
jq '.databases.mst.tables.mst_reward_groups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例4: 特定のカラム名を持つ全テーブルを調査

**シナリオ**: `reward_group_id`を持つすべてのテーブルを調べ、報酬が設定される場所を把握したい

```bash
# 1. reward_group_idを持つ全テーブルを検索
jq '.databases.mst.tables |
  to_entries |
  map(select(.value.columns | has("reward_group_id"))) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. 各テーブルでのreward_group_idの定義を確認
jq '.databases.mst.tables |
  to_entries |
  map(select(.value.columns | has("reward_group_id"))) |
  map({
    table: .key,
    nullable: .value.columns.reward_group_id.nullable,
    foreign_key: .value.columns.reward_group_id.foreign_key
  })' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例5: 多言語対応の実装を調べる

**シナリオ**: I18nテーブルの構造を理解し、多言語対応の実装パターンを把握したい

```bash
# 1. すべてのI18nテーブルを抽出
jq '.databases.mst.tables | keys | map(select(test("_i18n$")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. mst_units_i18nの構造を確認（代表例として）
jq '.databases.mst.tables.mst_units_i18n' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. I18nテーブル共通のカラムを確認
jq '.databases.mst.tables.mst_units_i18n.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
# 期待: ["id", "mst_unit_id", "language", "name", "description", "detail", ...]

# 4. languageカラムのenum値を確認（対応言語の一覧）
jq '.databases.mst.tables.mst_units_i18n.columns.language.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
# 期待: ["ja", "en", "zh-CN", "zh-TW", ...]
```

### 例6: CSV作成時のバリデーションルールを確認

**シナリオ**: `MstStage`のCSVを作成する前に、制約事項を確認したい

```bash
# 1. NOT NULLカラムを確認（必須項目）
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.nullable == false)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 2. enum型カラムとその取りうる値を確認
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.enum != null)) |
  map({column: .key, enum: .value.enum})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. 外部キーを確認（参照先テーブルのデータが必要）
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 4. デフォルト値を持つカラムを確認（省略可能）
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.default != null)) |
  map({column: .key, default: .value.default})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 例7: 外部キー依存関係を追跡する

**シナリオ**: `MstStage`を作成する際、どのテーブルのデータを先に作成する必要があるか調べたい

```bash
# 1. MstStageの外部キー一覧を取得
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 出力例:
# [
#   {"column": "mst_quest_id", "references": "mst_quests.id"},
#   {"column": "drop_group_id", "references": "mst_drop_groups.id"},
#   {"column": "first_clear_reward_group_id", "references": "mst_reward_groups.id"}
# ]

# 2. 依存先テーブル（MstQuest）のさらに依存関係を確認
jq '.databases.mst.tables.mst_quests.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# 3. 依存関係のツリーを構築
# MstStage
#   ├─ MstQuest（先に作成が必要）
#   ├─ MstDropGroup（先に作成が必要）
#   └─ MstRewardGroup（先に作成が必要）
```

## よくあるトラブルと対処法

### jqコマンドがエラーになる

**症状**: `jq: error (at <stdin>:1): Cannot index array with string "databases"`

**原因**: jqの構文エラー、またはJSONファイルのパスが間違っている

**対処法**:
1. ファイルパスを確認
2. jqの構文を確認（`,`や`|`の位置など）
3. `jq '.'`で全体構造を確認してから絞り込む

### テーブル名やカラム名が見つからない

**症状**: `null`が返ってくる

**原因**: テーブル名やカラム名のスペルミス、大文字小文字の違い

**対処法**:
1. テーブル名一覧を確認: `jq '.databases.mst.tables | keys'`
2. カラム名一覧を確認: `jq '.databases.mst.tables.テーブル名.columns | keys'`
3. 正確な名前を確認してから再実行

### enum値が見つからない

**症状**: カラムにenum値がない

**原因**: そのカラムはenum型ではない

**対処法**:
1. カラムの型を確認: `jq '.databases.mst.tables.テーブル名.columns.カラム名.type'`
2. enum型以外の場合は、既存データを参照して取りうる値を確認

## 関連ドキュメント

- [table-catalog.md](table-catalog.md) - 全165テーブルのカタログ
- [investigation-patterns.md](investigation-patterns.md) - よくある調査シナリオ
- [SKILL.md](../SKILL.md) - スキルの基本的な使い方
