# MstDungeonBlockReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonBlockReward.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonBlockReward` は**限界チャレンジの各ブロッククリア報酬を定義するテーブル**。

各ダンジョンブロックをクリアした際に付与されるリソース（コイン・アイテム・エンブレムなど）の種類と量を管理する。報酬カテゴリ（`Block` / `RandomBlock`）によって、確定報酬と抽選報酬を区別できる設計になっている。

2026年3月時点でCSVファイルは未作成（`MstDungeonBlockReward.csv` は存在しない）。

### ゲームプレイへの影響

- **`reward_category = Block`**: ブロッククリア時に確定で付与される報酬
- **`reward_category = RandomBlock`**: 複数の報酬候補から `percentage`（重み）に基づいて抽選で1つ付与される報酬
- 深度が深くなるほど `MstDungeonDepthSetting.block_reward_coefficient` が高くなり、同じ `resource_amount` でも実際の付与量が増加する

### 関連テーブルとの構造図

```
MstDungeon
  └─ id → MstDungeonBlock.mst_dungeon_id
                └─ id → MstDungeonBlockReward.mst_dungeon_block_id（1:N）
                              ├─ reward_category = Block      （確定報酬）
                              └─ reward_category = RandomBlock（抽選報酬、複数候補）

MstDungeonDepthSetting
  └─ block_reward_coefficient（ブロック報酬の倍率係数）
```

---

## 全カラム一覧

### mst_dungeon_block_rewards カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。報酬ID |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dungeon_block_id` | varchar(255) | 不可 | - | 参照先ブロックID（`mst_dungeon_blocks.id`） |
| `reward_category` | enum | 不可 | - | 報酬カテゴリ。`Block`（確定）/ `RandomBlock`（抽選）の2種 |
| `resource_type` | enum | 不可 | - | 報酬タイプ（ResourceType enum） |
| `resource_id` | varchar(255) | 許可 | - | 報酬リソースID。`Exp`・`Coin`・`FreeDiamond` の場合はNULL |
| `resource_amount` | int unsigned | 不可 | - | 報酬数量。`MstDungeonDepthSetting.block_reward_coefficient` で最終値が決まる |
| `percentage` | int unsigned | 不可 | 1 | 出現比重（`RandomBlock` の場合に抽選重みとして使用） |
| `sort_order` | int unsigned | 不可 | - | UI表示順序 |

---

## DungeonRewardCategory（報酬カテゴリ）

| 値 | 意味 | `percentage` の扱い |
|----|------|-------------------|
| `Block` | 確定報酬 | 無視される（全件付与） |
| `RandomBlock` | 抽選報酬 | 同一ブロック内の `RandomBlock` レコード間で重みとして使用。重みの合計を分母に各レコードの確率を計算 |

---

## ResourceType（報酬タイプ）

| 値 | 意味 | `resource_id` |
|----|------|-------------|
| `Exp` | 経験値 | NULL |
| `Coin` | コイン | NULL |
| `FreeDiamond` | 無料ダイヤ | NULL |
| `Item` | アイテム | `mst_items.id` |
| `Emblem` | エンブレム | `mst_emblems.id` |
| `Unit` | ユニット | `mst_units.id` |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeon_blocks` | `mst_dungeon_block_id` → `id` | 対応するフロアブロック |
| `mst_dungeon_depth_settings` | `mst_dungeon_id` 経由 | `block_reward_coefficient` で最終報酬量を算出 |
| `mst_items` | `resource_id` → `id` | `resource_type = Item` の場合に参照 |
| `mst_emblems` | `resource_id` → `id` | `resource_type = Emblem` の場合に参照 |
| `mst_units` | `resource_id` → `id` | `resource_type = Unit` の場合に参照 |

---

## 実データ例

> 2026年3月現在、`MstDungeonBlockReward.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 確定報酬（コイン付与）

```
ENABLE: e
id: dungeon_block_reward_001
release_key: 202601010
mst_dungeon_block_id: dungeon_00001_normal_01
reward_category: Block
resource_type: Coin
resource_id: (NULL)
resource_amount: 1000
percentage: 1
sort_order: 1
```

ブロッククリア時に必ずコイン1000枚（深度係数で補正後）を付与する。

### パターン2: 抽選報酬（複数候補から1つ）

```
ENABLE: e
id: dungeon_block_reward_002
release_key: 202601010
mst_dungeon_block_id: dungeon_00001_boss_01
reward_category: RandomBlock
resource_type: Item
resource_id: item_gacha_ticket_001
resource_amount: 1
percentage: 30
sort_order: 2

id: dungeon_block_reward_003
reward_category: RandomBlock
resource_type: FreeDiamond
resource_id: (NULL)
resource_amount: 50
percentage: 70
sort_order: 3
```

ボスブロッククリア時、確率30%でガチャチケット1枚、70%で無料ダイヤ50個のいずれかを付与する。

---

## 設定時のポイント

1. **`RandomBlock` は同一 `mst_dungeon_block_id` 内での `percentage` の合計が分母になる**。合計が100でなくても抽選は正常に動作するが、設計の明確化のため合計100に揃えることを推奨。

2. **深度係数（`block_reward_coefficient`）で報酬量が変動する**。深度が深くなるほど係数が大きくなるため、`resource_amount` は基本値（係数1.0時の量）として設定する。

3. **`resource_type` が `Exp`・`Coin`・`FreeDiamond` の場合は `resource_id` に NULL を設定する**。それ以外のリソースタイプ（`Item`・`Emblem`・`Unit`）は必ず有効な `resource_id` を設定する。

4. **`sort_order` はUI上の報酬表示順に使用される**。プレイヤーへの見せ方を考慮して、重要な報酬を上位に設定することを推奨。

5. **確定報酬と抽選報酬を組み合わせることができる**。同一ブロックに `Block`（確定）と `RandomBlock`（抽選）の両方のレコードを設定することで、「確定コイン + 抽選アイテム」のような複合報酬設定が可能。

6. **`MstDungeonBlock` を追加する際は、必ずブロック報酬も設定する**。報酬のないブロックはゲームバランス上問題になるため、最低限の報酬を定義すること。
