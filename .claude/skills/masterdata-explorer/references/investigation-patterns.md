# 調査パターン集

このドキュメントでは、マスタデータの調査でよくあるシナリオと、その具体的な手順を説明します。

## 目次

1. [新しいイベントを追加したい](#シナリオ1-新しいイベントを追加したい)
2. [ガチャの構造を理解したい](#シナリオ2-ガチャの構造を理解したい)
3. [ミッション報酬の仕組みを調べたい](#シナリオ3-ミッション報酬の仕組みを調べたい)
4. [特定のIDがどのテーブルにあるか探したい](#シナリオ4-特定のidがどのテーブルにあるか探したい)
5. [外部キー関係を追跡したい](#シナリオ5-外部キー関係を追跡したい)
6. [多言語対応の実装を調べたい](#シナリオ6-多言語対応の実装を調べたい)
7. [期間限定施策の設定方法を調べたい](#シナリオ7-期間限定施策の設定方法を調べたい)
8. [報酬グループの構造を理解したい](#シナリオ8-報酬グループの構造を理解したい)
9. [ステージのドロップ設定を調べたい](#シナリオ9-ステージのドロップ設定を調べたい)
10. [キャラクターの育成要素を調べたい](#シナリオ10-キャラクターの育成要素を調べたい)
11. [ショップ・課金パックの仕組みを調べたい](#シナリオ11-ショップ・課金パックの仕組みを調べたい)
12. [既存データの作り方を学びたい](#シナリオ12-既存データの作り方を学びたい)
13. [データ作成の依存関係を調べたい](#シナリオ13-データ作成の依存関係を調べたい)

## シナリオ1: 新しいイベントを追加したい

### 背景

新しい期間限定イベントを追加する際、どのテーブルにどのようなデータを追加すべきか知りたい。

### 調査手順

#### ステップ1: イベント関連テーブルを全て把握

```bash
# イベント関連のテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("event"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: OprEventの構造を確認

```bash
# OprEvent（イベント基本設定）の全カラムを確認
jq '.databases.mst.tables.opr_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: イベントタイプを確認

```bash
# event_typeのenum値を確認（どんなイベントタイプがあるか）
jq '.databases.mst.tables.opr_events.columns.event_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ4: 既存イベントデータを参照

```bash
# 既存のOprEventデータを確認（実例を学ぶ）
head -n 10 projects/glow-masterdata/OprEvent.csv
```

#### ステップ5: 関連テーブルを確認

```bash
# OprEventを参照している全テーブルを逆引き
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    columns: (
      .value.columns |
      to_entries |
      map(select(.value.foreign_key != null and (.value.foreign_key | test("opr_events")))) |
      map(.key)
    )
  }) |
  select(.columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 必要なテーブル

- **OprEvent**: イベントの基本設定
- **OprEventI18n**: 多言語対応（イベント名、説明文）
- **OprEventPoint**: ポイント報酬設定（ポイントイベントの場合）
- **OprEventRanking**: ランキング報酬設定（ランキングイベントの場合）
- **MstEventExchange**: イベント交換所設定
- **MstEventStory**: イベントストーリー設定
- **MstEventBoss**: イベントボス設定（レイドイベントの場合）

### 参照ドキュメント

- [table-catalog.md - イベント関連](table-catalog.md#7-イベント関連8テーブル)
- [table-catalog.md - テーブル関連図: イベントシステム](table-catalog.md#1-イベントシステム)

## シナリオ2: ガチャの構造を理解したい

### 背景

ガチャシステムの実装を理解し、新しいガチャを追加する際の参考にしたい。

### 調査手順

#### ステップ1: ガチャ関連テーブルを全て把握

```bash
# ガチャ関連のテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("gacha"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: OprGachaの構造を確認

```bash
# OprGacha（ガチャ基本設定）の全カラムを確認
jq '.databases.mst.tables.opr_gachas.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# ガチャタイプを確認
jq '.databases.mst.tables.opr_gachas.columns.gacha_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: ガチャラインナップの構造を確認

```bash
# MstGachaLineup（提供アイテム）の構造を確認
jq '.databases.mst.tables.mst_gacha_lineups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ4: 既存ガチャデータを参照

```bash
# 既存のOprGachaデータを確認
head -n 10 projects/glow-masterdata/OprGacha.csv

# 既存のMstGachaLineupデータを確認
head -n 10 projects/glow-masterdata/MstGachaLineup.csv
```

#### ステップ5: ステップアップガチャの仕組みを確認

```bash
# OprGachaStepの構造を確認
jq '.databases.mst.tables.opr_gacha_steps' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 必要なテーブル

- **OprGacha**: ガチャの基本設定
- **OprGachaI18n**: 多言語対応
- **MstGachaLineup**: 提供アイテム・キャラクター、排出率
- **OprGachaStep**: ステップアップガチャの段階設定
- **MstGachaPity**: 天井システム設定
- **MstGachaAnimation**: ガチャ演出設定

### 参照ドキュメント

- [table-catalog.md - ガチャ関連](table-catalog.md#4-ガチャ関連6テーブル)
- [table-catalog.md - テーブル関連図: ガチャシステム](table-catalog.md#2-ガチャシステム)

## シナリオ3: ミッション報酬の仕組みを調べたい

### 背景

ミッションシステムの報酬設定を理解し、新しいミッションを追加したい。

### 調査手順

#### ステップ1: ミッション関連テーブルを全て把握

```bash
# ミッション関連のテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("mission"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: MstMissionの構造を確認

```bash
# MstMission（恒常ミッション）の全カラムを確認
jq '.databases.mst.tables.mst_missions.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# ミッションタイプとターゲットタイプを確認
jq '.databases.mst.tables.mst_missions.columns.mission_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

jq '.databases.mst.tables.mst_missions.columns.target_type.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: 報酬グループの仕組みを確認

```bash
# MstRewardGroupの構造を確認
jq '.databases.mst.tables.mst_reward_groups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ4: 既存ミッションデータを参照

```bash
# 既存のMstMissionデータを確認
head -n 10 projects/glow-masterdata/MstMission.csv

# 既存のMstRewardGroupデータを確認
head -n 10 projects/glow-masterdata/MstRewardGroup.csv
```

### 必要なテーブル

- **MstMission**: 恒常ミッション定義
- **OprMission**: 期間限定ミッション定義
- **MstMissionI18n / OprMissionI18n**: 多言語対応
- **MstRewardGroup**: 報酬グループ（報酬の実体）
- **MstMissionDaily / MstMissionWeekly**: デイリー/ウィークリー設定
- **MstMissionEvent**: イベントミッション設定
- **MstMissionPanel**: ビンゴパネル設定

### 参照ドキュメント

- [table-catalog.md - ミッション関連](table-catalog.md#5-ミッション関連15テーブル)
- [table-catalog.md - テーブル関連図: ミッションシステム](table-catalog.md#3-ミッションシステム)

## シナリオ4: 特定のIDがどのテーブルにあるか探したい

### 背景

仕様書に「ID: 12345」という記述があるが、どのテーブルのIDか分からない。

### 調査手順

#### ステップ1: 既存データを全テーブルから検索

```bash
# 全CSVファイルからID:12345を検索
grep -r "^12345," projects/glow-masterdata/*.csv
```

#### ステップ2: テーブル名から推測

IDのプレフィックスや桁数から推測：
- `1000001` 〜 `1999999`: キャラクター系（MstCharacter）
- `2000001` 〜 `2999999`: アイテム系（MstItem）
- `3000001` 〜 `3999999`: ステージ系（MstStage）
- `4000001` 〜 `4999999`: ミッション系（MstMission）

#### ステップ3: 関連ドキュメントを確認

仕様書やSlackで言及されているテーブル名を確認し、そのテーブルのCSVを直接参照：

```bash
# MstCharacterの既存データを確認
head -n 20 projects/glow-masterdata/MstCharacter.csv | grep "12345"
```

### トラブルシューティング

- **IDが見つからない**: まだ作成されていないデータの可能性
- **複数のテーブルで見つかる**: 外部キー参照の可能性（参照先と参照元の両方でヒット）

## シナリオ5: 外部キー関係を追跡したい

### 背景

MstStageを作成する際、依存している他のテーブルのデータを先に作成する必要があるか知りたい。

### 調査手順

#### ステップ1: 対象テーブルの外部キーを確認

```bash
# MstStageの外部キー一覧を取得
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

#### ステップ2: 依存先テーブルの外部キーをさらに確認

```bash
# MstQuestの外部キー一覧を取得
jq '.databases.mst.tables.mst_quests.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: 依存関係ツリーを構築

MstStageの依存関係：
```
MstStage
  ├─ MstQuest（先に作成が必要）
  ├─ MstDropGroup（先に作成が必要）
  └─ MstRewardGroup（先に作成が必要）
```

#### ステップ4: NULL許可を確認（省略可能な外部キー）

```bash
# MstStageのNULL許可カラムを確認
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.nullable == true and .value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**NULL許可の外部キーは、データ作成時に`__NULL__`を指定可能**

### データ作成順序の決定

1. 依存先テーブル（MstQuest, MstDropGroup, MstRewardGroup）を先に作成
2. NULL許可の外部キーは後回しでも可
3. MstStageを作成

## シナリオ6: 多言語対応の実装を調べたい

### 背景

新しいテーブルに多言語対応を追加する際、どのような構造にすべきか知りたい。

### 調査手順

#### ステップ1: 全I18nテーブルを確認

```bash
# すべてのI18nテーブルを抽出
jq '.databases.mst.tables | keys | map(select(test("_i18n$")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: 代表例（MstCharacterI18n）の構造を確認

```bash
# MstCharacterI18nの全カラムを確認
jq '.databases.mst.tables.mst_character_i18n.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**共通カラム**:
- `mst_character_id`: 親テーブルのID（外部キー）
- `language`: 言語コード
- `name`: 多言語の名称
- `description`: 多言語の説明文

#### ステップ3: 対応言語を確認

```bash
# languageカラムのenum値を確認
jq '.databases.mst.tables.mst_character_i18n.columns.language.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**: `["ja", "en", "zh-CN", "zh-TW"]`

#### ステップ4: 既存I18nデータを参照

```bash
# 既存のMstCharacterI18nデータを確認
head -n 20 projects/glow-masterdata/MstCharacterI18n.csv
```

### I18nテーブルの設計パターン

1. 親テーブル（例: MstCharacter）に`id`のみを持つ
2. I18nテーブル（例: MstCharacterI18n）に多言語テキストを持つ
3. I18nテーブルは`(親テーブルID, language)`の複合主キー
4. 各言語ごとに1レコードずつ作成

## シナリオ7: 期間限定施策の設定方法を調べたい

### 背景

期間限定の施策（イベント、ガチャ、ミッション）を設定する際、`Opr*`テーブルの使い方を知りたい。

### 調査手順

#### ステップ1: Opr*テーブルを全て確認

```bash
# Opr*プレフィックスのテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("^opr_")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: 代表例（OprEvent）の構造を確認

```bash
# OprEventの全カラムを確認
jq '.databases.mst.tables.opr_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**期間設定カラム**:
- `start_at`: 開始日時
- `end_at`: 終了日時

#### ステップ3: 既存Opr*データを参照

```bash
# 既存のOprEventデータを確認
head -n 10 projects/glow-masterdata/OprEvent.csv
```

### Opr*テーブルの特徴

- **期間限定**: `start_at`, `end_at`カラムで期間を設定
- **運営データ**: 恒常データ（Mst*）と区別される
- **参照関係**: Mst*テーブルを参照することが多い（例: OprEvent → MstEventType）

### 主要なOpr*テーブル

- **OprEvent**: 期間限定イベント
- **OprGacha**: 期間限定ガチャ
- **OprMission**: 期間限定ミッション
- **OprProduct**: 期間限定商品
- **OprBattlePass**: バトルパス

## シナリオ8: 報酬グループの構造を理解したい

### 背景

様々な場所で使われる`MstRewardGroup`の仕組みを理解したい。

### 調査手順

#### ステップ1: MstRewardGroupの構造を確認

```bash
# MstRewardGroupの全カラムを確認
jq '.databases.mst.tables.mst_reward_groups.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**重要カラム**:
- `id`: 報酬グループID
- `reward_items`: JSON形式の報酬アイテム配列

#### ステップ2: reward_itemsの構造を既存データで確認

```bash
# 既存のMstRewardGroupデータを確認
head -n 10 projects/glow-masterdata/MstRewardGroup.csv
```

**reward_itemsのJSON形式例**:
```json
[
  {"type": "character", "id": 1000001, "amount": 1},
  {"type": "item", "id": 2000001, "amount": 10},
  {"type": "currency", "id": 1, "amount": 1000}
]
```

#### ステップ3: MstRewardGroupを参照している全テーブルを確認

```bash
# reward_group_idを持つ全テーブルを検索
jq '.databases.mst.tables |
  to_entries |
  map(select(.value.columns | has("reward_group_id"))) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### MstRewardGroupの使用例

- **MstStage**: 初回クリア報酬
- **MstMission**: ミッション達成報酬
- **OprEventPoint**: イベントポイント報酬
- **MstAdventBattleReward**: 降臨バトル報酬
- **MstPack**: 課金パック内容

## シナリオ9: ステージのドロップ設定を調べたい

### 背景

ステージクリア時のドロップアイテム設定方法を理解したい。

### 調査手順

#### ステップ1: MstStageのdrop_group_idを確認

```bash
# MstStageのdrop_group_idカラムを確認
jq '.databases.mst.tables.mst_stages.columns.drop_group_id' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: MstDropGroupの構造を確認

```bash
# MstDropGroupの全カラムを確認
jq '.databases.mst.tables.mst_drop_groups.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**重要カラム**:
- `id`: ドロップグループID
- `drop_items`: JSON形式のドロップアイテム配列
- `drop_rates`: 各アイテムのドロップ確率
- `guaranteed_items`: 確定ドロップアイテム

#### ステップ3: 既存ドロップグループデータを参照

```bash
# 既存のMstDropGroupデータを確認
head -n 10 projects/glow-masterdata/MstDropGroup.csv
```

### ドロップ設定の構造

**drop_itemsのJSON形式例**:
```json
[
  {"item_id": 2000001, "min_amount": 1, "max_amount": 3, "drop_rate": 0.5},
  {"item_id": 2000002, "min_amount": 1, "max_amount": 1, "drop_rate": 0.3},
  {"item_id": 2000003, "min_amount": 5, "max_amount": 10, "drop_rate": 0.2}
]
```

**guaranteed_itemsのJSON形式例**:
```json
[
  {"item_id": 2000004, "amount": 1}
]
```

## シナリオ10: キャラクターの育成要素を調べたい

### 背景

キャラクターの育成システム（レベルアップ、覚醒、進化）を理解したい。

### 調査手順

#### ステップ1: MstCharacterの構造を確認

```bash
# MstCharacterの全カラムを確認
jq '.databases.mst.tables.mst_characters.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**育成関連カラム**:
- `max_level`: 最大レベル
- `base_hp`, `base_attack`, `base_defense`: 基礎ステータス
- `growth_rate`: 成長率

#### ステップ2: 覚醒システムを確認

```bash
# MstCharacterAwakeningの構造を確認
jq '.databases.mst.tables.mst_character_awakenings' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**覚醒設定**:
- `mst_character_id`: キャラクターID
- `awakening_level`: 覚醒レベル
- `required_item_id`: 必要素材ID
- `required_amount`: 必要数量

#### ステップ3: 進化システムを確認

```bash
# MstCharacterEvolutionの構造を確認
jq '.databases.mst.tables.mst_character_evolutions' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**進化設定**:
- `from_character_id`: 進化前キャラクターID
- `to_character_id`: 進化後キャラクターID
- `required_items`: 必要素材（JSON配列）

### キャラクター育成の全体像

```
MstCharacter（基本情報）
  ├─ レベルアップ（max_level, growth_rate）
  ├─ MstCharacterAwakening（覚醒）
  │    └─ 必要素材（MstItem）
  └─ MstCharacterEvolution（進化）
       └─ 必要素材（MstItem）
```

## シナリオ11: ショップ・課金パックの仕組みを調べたい

### 背景

ショップと課金パックの設定方法を理解したい。

### 調査手順

#### ステップ1: ショップ関連テーブルを確認

```bash
# ショップ関連のテーブルを全検索
jq '.databases.mst.tables | keys | map(select(test("shop|pack|product"; "i")))' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: MstShopの構造を確認

```bash
# MstShopの全カラムを確認
jq '.databases.mst.tables.mst_shops.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: MstPackの構造を確認

```bash
# MstPackの全カラムを確認
jq '.databases.mst.tables.mst_packs.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**重要カラム**:
- `id`: パックID
- `product_id`: アプリ内課金のプロダクトID
- `price`: 価格
- `reward_group_id`: 報酬内容（MstRewardGroup参照）

#### ステップ4: OprProductで期間限定商品を確認

```bash
# OprProductの構造を確認
jq '.databases.mst.tables.opr_products' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### ショップシステムの構造

```
MstShop（ショップ）
  └─ MstShopItem（ショップアイテム）
       ├─ MstItem（アイテム）
       └─ MstCurrency（通貨）

MstPack（課金パック）
  ├─ MstPackContent（パック内容）
  │    └─ MstRewardGroup（報酬）
  └─ OprProduct（期間限定商品）
```

## シナリオ12: 既存データの作り方を学びたい

### 背景

新しいテーブルのCSVを作成する際、既存データの書き方を参考にしたい。

### 調査手順

#### ステップ1: CSVテンプレートの列順を確認

```bash
# MstCharacterのCSVテンプレート（列名定義行）
sed -n '2p' projects/glow-masterdata/sheet_schema/MstCharacter.csv
```

**CSVテンプレートの構造**:
- 1行目: メモ行（`memo`から始まる）
- 2行目: テーブル名行（`TABLE,MstCharacter`など）
- 3行目: ENABLE行（`ENABLE,1,1,1,...`）
- 4行目: カラム名行（`id,name,rarity_id,...`）

#### ステップ2: 既存データの実例を確認

```bash
# 既存のMstCharacterデータ（先頭5行）
head -n 9 projects/glow-masterdata/MstCharacter.csv
```

**確認ポイント**:
- NULL値の表現: `__NULL__`
- 改行の表現: `\n`（実際の改行文字ではなくエスケープシーケンス）
- 文字列のダブルクォート処理

#### ステップ3: DBスキーマで制約を確認

```bash
# NOT NULLカラムを確認
jq '.databases.mst.tables.mst_characters.columns |
  to_entries |
  map(select(.value.nullable == false)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# enum値を確認
jq '.databases.mst.tables.mst_characters.columns |
  to_entries |
  map(select(.value.enum != null)) |
  map({column: .key, enum: .value.enum})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### CSVデータ作成の注意点

1. **列順はCSVテンプレートに厳密に従う**（`sheet_schema/`の4行目）
2. **改行は`\n`エスケープシーケンスを使用**（実際の改行文字は禁止）
3. **NULL値は`__NULL__`を使用**（ただし`nullable: false`のカラムでは使用禁止）
4. **enum値はDBスキーマで確認**（それ以外の値は無効）
5. **外部キー参照先のデータを先に作成**

## シナリオ13: データ作成の依存関係を調べたい

### 背景

複数のテーブルのCSVを作成する際、どの順序で作成すべきか知りたい。

### 調査手順

#### ステップ1: 各テーブルの外部キーを確認

```bash
# MstStageの外部キー一覧を取得
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ2: 依存関係ツリーを構築

**例: MstStageを作成する場合**

```
MstStage
  ├─ MstQuest（先に作成が必要）
  ├─ MstDropGroup（先に作成が必要）
  │    └─ MstItem（先に作成が必要）
  └─ MstRewardGroup（先に作成が必要）
       ├─ MstCharacter（先に作成が必要）
       ├─ MstItem（先に作成が必要）
       └─ MstCurrency（先に作成が必要）
```

#### ステップ3: NULL許可の外部キーを確認

```bash
# MstStageのNULL許可外部キーを確認
jq '.databases.mst.tables.mst_stages.columns |
  to_entries |
  map(select(.value.nullable == true and .value.foreign_key != null)) |
  map({column: .key, references: .value.foreign_key})' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**NULL許可の外部キーは後回しでも可**（`__NULL__`を指定できる）

### データ作成順序の決定方法

1. **外部キーを持たないテーブル**を最初に作成（MstItem, MstCurrency, MstCharacter など）
2. **1段階の依存関係**を持つテーブルを次に作成（MstRewardGroup, MstDropGroup など）
3. **複数段階の依存関係**を持つテーブルを最後に作成（MstStage など）
4. **NULL許可の外部キー**は後回しにしても良い

## トラブルシューティング

### よくあるエラーと対処法

#### エラー1: 外部キー制約違反

**症状**: CSV投入時に「foreign key constraint fails」エラー

**原因**: 参照先のデータが存在しない

**対処法**:
1. 参照先テーブル（例: MstRewardGroup）を先に作成
2. 参照先のIDが正しいか確認
3. NULL許可の場合は`__NULL__`を指定

#### エラー2: enum値が無効

**症状**: CSV投入時に「invalid enum value」エラー

**原因**: DBスキーマで定義されていないenum値を指定

**対処法**:
1. DBスキーマでenum値を確認: `jq '.databases.mst.tables.テーブル名.columns.カラム名.enum'`
2. 正しいenum値を指定

#### エラー3: NOT NULL制約違反

**症状**: CSV投入時に「column cannot be null」エラー

**原因**: `nullable: false`のカラムに`__NULL__`を指定

**対処法**:
1. DBスキーマで`nullable`を確認
2. `nullable: false`の場合は必ず値を指定

#### エラー4: 列数が合わない

**症状**: CSV投入時に「column count mismatch」エラー

**原因**: CSVテンプレートの列順と異なる

**対処法**:
1. CSVテンプレート（`sheet_schema/`）の4行目を確認
2. 列順を完全に一致させる

## 関連ドキュメント

- [SKILL.md](../SKILL.md) - スキルの基本的な使い方
- [table-catalog.md](table-catalog.md) - 全165テーブルのカタログ
- [schema-reference.md](schema-reference.md) - DBスキーマの詳細な調査方法
