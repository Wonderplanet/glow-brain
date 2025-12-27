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

#### ステップ2: mst_eventsの構造を確認

```bash
# mst_events（イベント基本設定）の全カラムを確認
jq '.databases.mst.tables.mst_events.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: 既存イベントデータを参照

```bash
# 既存のMstEvent.csvデータを確認（実例を学ぶ）
head -n 10 projects/glow-masterdata/MstEvent.csv
```

#### ステップ4: 関連テーブルを確認

```bash
# mst_eventsを参照している全テーブルを逆引き
jq '.databases.mst.tables |
  to_entries |
  map({
    table: .key,
    columns: (
      .value.columns |
      to_entries |
      map(select(.value.foreign_key != null and (.value.foreign_key | test("mst_events")))) |
      map(.key)
    )
  }) |
  select(.columns | length > 0)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 必要なテーブル

**注**: DBスキーマではsnake_case（`mst_events`）、CSVファイル名はPascalCase（`MstEvent.csv`）を使用します。

- **mst_events** (MstEvent.csv): イベントの基本設定
- **mst_events_i18n** (MstEventI18n.csv): 多言語対応（イベント名、説明文）
- **mst_event_bonus_units** (MstEventBonusUnit.csv): イベントボーナスユニット設定
- **mst_event_display_rewards** (MstEventDisplayReward.csv): イベント表示報酬設定
- **mst_mission_events** (MstMissionEvent.csv): ミッションイベント設定

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
# opr_gachas（ガチャ基本設定）の全カラムを確認
jq '.databases.mst.tables.opr_gachas.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# ガチャタイプを確認
jq '.databases.mst.tables.opr_gachas.columns.gacha_type' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ3: ガチャ賞品の構造を確認

```bash
# opr_gacha_prizes（ガチャ賞品）の構造を確認
jq '.databases.mst.tables.opr_gacha_prizes.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

#### ステップ4: 既存ガチャデータを参照

```bash
# 既存のOprGacha.csvデータを確認
head -n 10 projects/glow-masterdata/OprGacha.csv

# 既存のOprGachaPrize.csvデータを確認
head -n 10 projects/glow-masterdata/OprGachaPrize.csv
```

#### ステップ5: その他のガチャ関連テーブルを確認

```bash
# ガチャで使用するリソース
jq '.databases.mst.tables.opr_gacha_use_resources.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# ボックスガチャ（別タイプ）
jq '.databases.mst.tables.mst_box_gachas.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

### 必要なテーブル

**注**: DBスキーマではsnake_case、CSVファイル名はPascalCaseを使用します。

- **opr_gachas** (OprGacha.csv): ガチャの基本設定
- **opr_gachas_i18n** (OprGachaI18n.csv): 多言語対応
- **opr_gacha_prizes** (OprGachaPrize.csv): ガチャ賞品・排出率
- **opr_gacha_use_resources** (OprGachaUseResource.csv): ガチャ使用リソース
- **mst_box_gachas** (MstBoxGacha.csv): ボックスガチャ設定（通常ガチャとは別タイプ）

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
- `1000001` 〜 `1999999`: ユニット系（mst_units）
- `2000001` 〜 `2999999`: アイテム系（mst_items）
- `3000001` 〜 `3999999`: ステージ系（mst_stages）
- `4000001` 〜 `4999999`: ミッション系（mst_missions）

**注意**: これは一般的なパターンであり、実際のID体系は異なる場合があります。

#### ステップ3: 関連ドキュメントを確認

仕様書やSlackで言及されているテーブル名を確認し、そのテーブルのCSVを直接参照：

```bash
# mst_unitsの既存データを確認（例）
head -n 20 projects/glow-masterdata/MstUnit.csv | grep "12345" 2>/dev/null || echo "ファイルが存在しない、または該当IDなし"
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

#### ステップ2: 代表例（MstUnitsI18n）の構造を確認

```bash
# MstUnitsI18nの全カラムを確認
jq '.databases.mst.tables.mst_units_i18n.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**共通カラム**:
- `id`: I18nテーブル自身のID
- `mst_unit_id`: 親テーブル（mst_units）のID（外部キー）
- `language`: 言語コード
- `name`: 多言語の名称
- `description`: 多言語の説明文
- `detail`: 詳細説明

#### ステップ3: 対応言語を確認

```bash
# languageカラムのenum値を確認
jq '.databases.mst.tables.mst_units_i18n.columns.language.enum' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**出力例**: `["ja", "en", "zh-CN", "zh-TW"]`

#### ステップ4: 既存I18nデータを参照

```bash
# 既存のMstUnitsI18nデータを確認（存在する場合）
head -n 20 projects/glow-masterdata/MstUnitsI18n.csv 2>/dev/null || echo "ファイルが存在しない場合は、CSVテンプレートを確認: projects/glow-masterdata/sheet_schema/MstUnitsI18n.csv"
```

### I18nテーブルの設計パターン

1. 親テーブル（例: mst_units）に`id`とデータ本体を持つ
2. I18nテーブル（例: mst_units_i18n）に多言語テキストを持つ
3. I18nテーブルは`id`（主キー）と`mst_unit_id`（外部キー）、`language`を持つ
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

#### ステップ2: 代表例（opr_gachas）の構造を確認

```bash
# opr_gachasの全カラムを確認
jq '.databases.mst.tables.opr_gachas.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**期間設定カラム**:
- `start_at`: 開始日時
- `end_at`: 終了日時

#### ステップ3: 既存Opr*データを参照

```bash
# 既存のOprGacha.csvデータを確認
head -n 10 projects/glow-masterdata/OprGacha.csv
```

### Opr*テーブルの特徴

- **期間限定**: `start_at`, `end_at`カラムで期間を設定
- **運営データ**: 恒常データ（Mst*）と区別される
- **参照関係**: Mst*テーブルを参照することが多い

### 主要なOpr*テーブル

**注**: DBスキーマではsnake_case、CSVファイル名はPascalCaseを使用します。

- **opr_gachas** (OprGacha.csv): 期間限定ガチャ
- **opr_products** (OprProduct.csv): 期間限定商品
- **opr_campaigns** (OprCampaign.csv): キャンペーン設定
- **mst_events** (MstEvent.csv): イベント設定（Mst側で定義）

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

## シナリオ10: ユニットの育成要素を調べたい

### 背景

ユニットの育成システム（レベルアップ、グレードアップ、ランクアップ）を理解したい。

### 調査手順

#### ステップ1: MstUnitsの構造を確認

```bash
# MstUnitsの全カラムを確認
jq '.databases.mst.tables.mst_units.columns | keys' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**育成関連カラム**:
- `max_hp`: 最大HP
- `min_hp`: 最小HP
- `max_attack_power`: 最大攻撃力
- `min_attack_power`: 最小攻撃力
- `rarity`: レアリティ
- `fragment_mst_item_id`: 欠片アイテムID

#### ステップ2: グレードアップシステムを確認

```bash
# MstUnitGradeUpsの構造を確認
jq '.databases.mst.tables.mst_unit_grade_ups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**グレードアップ設定**:
ユニットのグレード（ランク）を上げるための設定を定義

#### ステップ3: ランクアップシステムを確認

```bash
# MstUnitRankUpsの構造を確認
jq '.databases.mst.tables.mst_unit_rank_ups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**ランクアップ設定**:
ユニットのランクを上げるための設定を定義

#### ステップ4: レベルアップシステムを確認

```bash
# MstUnitLevelUpsの構造を確認
jq '.databases.mst.tables.mst_unit_level_ups' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json
```

**レベルアップ設定**:
ユニットのレベルアップに必要な経験値や素材を定義

### ユニット育成の全体像

```
mst_units（ユニット基本情報）
  ├─ mst_unit_level_ups（レベルアップ）
  ├─ mst_unit_grade_ups（グレードアップ）
  ├─ mst_unit_rank_ups（ランクアップ）
  ├─ mst_unit_specific_rank_ups（特定ユニットのランクアップ）
  └─ mst_unit_abilities（ユニットアビリティ）
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
# MstUnitのCSVテンプレート（列名定義行）
sed -n '2p' projects/glow-masterdata/sheet_schema/MstUnit.csv
```

**CSVテンプレートの構造**:
- 1行目: メモ行（`memo`から始まる）
- 2行目: テーブル名行（`TABLE,MstUnit`など）
- 3行目: ENABLE行（`ENABLE,1,1,1,...`）
- 4行目: カラム名行（`id,name,rarity,...`）

#### ステップ2: 既存データの実例を確認

```bash
# 既存のMstUnitデータ（先頭5行、存在する場合）
head -n 9 projects/glow-masterdata/MstUnit.csv 2>/dev/null || echo "ファイルが存在しない場合は別のテーブルを参照"
```

**確認ポイント**:
- NULL値の表現: `__NULL__`
- 改行の表現: `\n`（実際の改行文字ではなくエスケープシーケンス）
- 文字列のダブルクォート処理

#### ステップ3: DBスキーマで制約を確認

```bash
# NOT NULLカラムを確認
jq '.databases.mst.tables.mst_units.columns |
  to_entries |
  map(select(.value.nullable == false)) |
  map(.key)' \
  projects/glow-server/api/database/schema/exports/master_tables_schema.json

# enum値を確認
jq '.databases.mst.tables.mst_units.columns |
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
mst_stages
  ├─ mst_quests（先に作成が必要）
  ├─ mst_drop_groups（先に作成が必要）
  │    └─ mst_items（先に作成が必要）
  └─ mst_reward_groups（先に作成が必要）
       ├─ mst_units（先に作成が必要）
       ├─ mst_items（先に作成が必要）
       └─ mst_currencies（先に作成が必要）
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

1. **外部キーを持たないテーブル**を最初に作成（mst_items, mst_currencies, mst_units など）
2. **1段階の依存関係**を持つテーブルを次に作成（mst_reward_groups, mst_drop_groups など）
3. **複数段階の依存関係**を持つテーブルを最後に作成（mst_stages など）
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
