# MstItem 詳細説明

> CSVパス: `projects/glow-masterdata/MstItem.csv`
> i18n CSVパス: `projects/glow-masterdata/MstItemI18n.csv`

---

## 概要

`MstItem` は**ゲーム内に存在するすべてのアイテムを管理するマスタテーブル**。ガチャチケット・キャラのかけら・ランクアップ素材・かけらBOX・ガチャメダル・スタミナ回復アイテムなど、プレイヤーが所持できるすべての種類のアイテムを1テーブルで一元管理する。多言語のアイテム名と説明文は `MstItemI18n` で管理する。

### ゲームプレイへの影響

- **type** がアイテムの機能的な種類を決定する。使用時の効果はこのタイプに基づいてサーバー・クライアントが処理する。
- **group_type** がアプリのアイテム一覧画面のタブ分類に使用される（`Consumable` = 消費アイテム、`Etc` = その他）。
- **rarity** がアイテムのレアリティ表示に使用される（N / R / SR / SSR / UR）。
- **asset_key** でアイテムアイコン画像などのAddressablesアセットを参照する。
- **effect_value** は特定の `type`（例: `RankUpMaterial`）でのみ使用される追加パラメータ（カラー属性など）。
- **is_visible** がアイテム一覧への表示/非表示を制御する（1=表示、0=非表示）。
- **start_date / end_date** でアイテムの有効期間を管理する。
- **destination_opr_product_id** で購入導線の遷移先商品IDを設定できる（未使用の場合はNULL）。

### 関連テーブルとの構造図

```
MstItem（アイテム本体）
  └─ id → MstItemI18n.mst_item_id（多言語名称・説明文）
  └─ id → MstExchangeReward.resource_id（交換所の報酬として設定）
  └─ id → MstExchangeCost.cost_id（交換所のコストとして設定）
  └─ id → MstFragmentBox.mst_item_id（type = RandomFragmentBox / SelectionFragmentBox 時）
  └─ id → MstFragmentBoxGroup.mst_item_id（かけらBOXのラインナップアイテム）
  └─ id → MstIdleIncentiveItem.mst_item_id（探索報酬のアイテム）
  └─ id → MstItemRarityTrade（type = CharacterFragment のかけら交換設定）
```

---

## 全カラム一覧

### mst_items カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（UUID）。種別と識別子を組み合わせた文字列 |
| `type` | enum | 可 | - | アイテムの機能種別（`ItemType` enum） |
| `group_type` | varchar(255) | 不可 | - | UI上のタブ分類（`ItemGroupType` enum） |
| `rarity` | enum | 不可 | - | レアリティ（N / R / SR / SSR / UR） |
| `asset_key` | varchar(255) | 不可 | - | アイコン等のAddressablesアセットキー |
| `effect_value` | varchar(255) | 可 | - | 特定タイプでの効果値（例: RankUpMaterial のカラー属性） |
| `mst_series_id` | varchar(255) | 不可 | "" | 紐付けるシリーズID（`mst_series.id`）。未設定は空文字 |
| `sort_order` | int | 不可 | 0 | アイテム一覧内の表示順 |
| `is_visible` | tinyint unsigned | 不可 | 1 | 表示フラグ（1=表示、0=非表示） |
| `start_date` | timestamp | 不可 | - | アイテムの有効開始日 |
| `end_date` | timestamp | 不可 | - | アイテムの有効終了日 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `destination_opr_product_id` | varchar(255) | 不可 | "" | 購入導線の遷移先OprProductId |

### MstItemI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー。`{mst_item_id}_{language}` 形式 |
| `mst_item_id` | varchar(255) | 不可 | - | 対応するアイテムID（`mst_items.id`） |
| `language` | enum | 不可 | ja | 言語コード（`ja` / `en` / `zh-Hant`） |
| `name` | varchar(255) | 不可 | - | アイテム名（UI表示用） |
| `description` | varchar(255) | 不可 | - | アイテム説明文（UI表示用） |

---

## ItemType（アイテム種別）

| 値 | 説明 |
|----|------|
| `CharacterFragment` | キャラのかけら。ユニット召喚に使用する素材 |
| `RankUpMaterial` | ランクアップ素材（カラーメモリー）。キャラのLv上限開放に使用 |
| `RankUpMemoryFragment` | ランクアップメモリーフラグメント |
| `StageMedal` | ステージクリアメダル |
| `IdleCoinBox` | 探索コインBOX。開封するとコインが入手できる |
| `IdleRankUpMaterialBox` | 探索ランクアップ素材BOX |
| `RandomFragmentBox` | ランダムかけらBOX。開封するとランダムなキャラのかけらが入手できる |
| `SelectionFragmentBox` | 選択式かけらBOX。開封するかけらをプレイヤーが選択できる |
| `GachaTicket` | ガチャチケット。ガチャを引くのに使用する |
| `GachaMedal` | ガチャメダル |
| `StaminaRecoveryPercent` | スタミナ回復（割合） |
| `StaminaRecoveryFixed` | スタミナ回復（固定量） |
| `SeriesPoint` | シリーズポイント |
| `Etc` | その他（分類に当てはまらないアイテム） |
| `ArtworkGradeUpMaterial` | アートワークグレードアップ素材 |
| `ArtworkGradeUpSeriesMaterial` | アートワークグレードアップシリーズ素材 |

---

## Rarity（レアリティ）

| 値 | 説明 |
|----|------|
| `N` | ノーマル |
| `R` | レア |
| `SR` | スーパーレア |
| `SSR` | スーパースーパーレア |
| `UR` | ウルトラレア |

---

## 命名規則 / IDの生成ルール

| 種類 | 命名パターン | 例 |
|------|------------|-----|
| ランクアップ素材 | `memory_{シリーズコード}_{5桁連番}` | `memory_glo_00001` |
| キャラかけら | `piece_{キャラコード}_{5桁連番}` | `piece_aka_00001` |
| ガチャチケット | `ticket_{シリーズコード}_{5桁連番}` | `ticket_glo_00001` |
| かけらBOX | `box_{シリーズコード}_{5桁連番}` | `box_glo_00001` |
| i18n id | `{mst_item_id}_{language}` | `memory_glo_00001_ja` |

---

## 他テーブルとの連携

| 連携先テーブル | カラム | 関係 |
|-------------|-------|------|
| `mst_items_i18n` | `id` → `mst_item_id` | 多言語名称・説明文（1:N） |
| `mst_series` | `mst_series_id` → `id` | シリーズ紐付け（N:1） |
| `mst_fragment_boxes` | `id` → `mst_item_id` | BOXアイテムのラインナップ設定（1:1） |
| `mst_fragment_box_groups` | `id` → `mst_item_id` | かけらラインナップ（N:1） |
| `mst_idle_incentive_items` | `id` → `mst_item_id` | 探索報酬アイテム（N:1） |
| `mst_exchange_costs` | `id` → `cost_id` | 交換コスト（N:1） |
| `mst_exchange_rewards` | `id` → `resource_id` | 交換報酬（N:1） |

---

## 実データ例

**パターン1: ランクアップ素材（カラーメモリー）**
```
ENABLE: e
id: memory_glo_00001
type: RankUpMaterial
group_type: Etc
rarity: UR
asset_key: memory_glo_00001
effect_value: Colorless
sort_order: 1
start_date: 2024-01-01 00:00:00
end_date: 2037-12-31 23:59:59
release_key: 202509010
item_type: RankUpMaterial
destination_opr_product_id: NULL
```
```
（MstItemI18n）
id: memory_glo_00001_ja
mst_item_id: memory_glo_00001
language: ja
name: カラーメモリー・グレー
description: 無属性キャラのLv.上限開放に使用するアイテム
release_key: 202509010
```

**パターン2: ガチャチケット（UR）**
```
ENABLE: e
id: ticket_glo_00002
type: GachaTicket
group_type: Consumable
rarity: UR
asset_key: ticket_glo_00002
effect_value: NULL
sort_order: 49
start_date: 2024-01-01 00:00:00
end_date: 2037-12-31 23:59:59
release_key: 202509010
item_type: GachaTicket
destination_opr_product_id: NULL
```

---

## 設定時のポイント

1. **type と group_type の組み合わせ**: `Consumable`（消費アイテムタブ）に分類するのはGachaTicket・RandomFragmentBox・SelectionFragmentBoxなど消費して使うアイテム。ランクアップ素材やかけらは `Etc` に分類する。
2. **effect_value の用途**: `RankUpMaterial` タイプでは `effect_value` にカラー属性（`Colorless`, `Red`, `Blue`, `Yellow`, `Green` など）を文字列で設定する。他のタイプでは通常NULLまたは空欄。
3. **is_visible の管理**: プレイヤーに見せたくないシステム内部アイテムは `is_visible = 0` に設定する。デフォルトは1（表示）。
4. **i18n の言語設定**: 現在CSVでは `ja` のみのレコードが存在（202件）。スキーマ定義では `en` / `zh-Hant` もサポートしているが未登録。
5. **start_date / end_date の設定**: 常設アイテムは `end_date` を `2037-12-31 23:59:59` などの遠い未来に設定する。期間限定アイテムは終了日時と合わせる。
6. **rarity の設計**: アイテムの希少性・重要度に合わせてレアリティを設定する。UI上のレアリティ枠色表示に影響する。
7. **MstItemRarityTrade との連携**: `type = CharacterFragment` のかけらアイテムは、`MstItemRarityTrade` でかけらBOXへの交換レートが管理される。かけらを追加する際はこの設定も確認する。
