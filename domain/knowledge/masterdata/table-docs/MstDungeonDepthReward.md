# MstDungeonDepthReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstDungeonDepthReward.csv`（未作成・将来追加予定）

---

## 概要

`MstDungeonDepthReward` は**限界チャレンジのフロア到達報酬（マイルストーン報酬）を定義するテーブル**。

通常のブロッククリア報酬（`MstDungeonBlockReward`）とは異なり、特定の深度（フロア数）に到達した際に付与される一時的な大型報酬。例えば「深度10到達でダイヤ50個」といったマイルストーン的な報酬設計に使用する。深度帯ごとに複数の報酬を定義でき、`sort_order` で表示順を制御する。

2026年3月時点でCSVファイルは未作成。

### ゲームプレイへの影響

- **`min_depth`**: この深度に到達した時点で報酬を付与する（ブロックごとの積み重ね報酬ではなく、指定深度到達時の一回限りの報酬）
- **`resource_type`** / **`resource_amount`**: 付与するリソースの種類と量
- 到達報酬は各深度で初回到達時のみ付与（リセットした場合の再付与ルールはサーバー側で制御）

### 関連テーブルとの構造図

```
MstDungeon（開催回）
  └─ id → MstDungeonDepthReward.mst_dungeon_id（1:N、深度到達報酬）
                └─ min_depth ごとに複数のリソース報酬を定義
                └─ sort_order で表示・付与順を制御
```

`MstDungeonBlockReward`（ブロッククリア報酬）との違い:
- `MstDungeonBlockReward`: 各ブロッククリアのたびに付与
- `MstDungeonDepthReward`: 特定深度に初めて到達したときに付与（マイルストーン）

---

## 全カラム一覧

### mst_dungeon_depth_rewards カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `mst_dungeon_id` | varchar(255) | 不可 | - | 参照先ダンジョンID（`mst_dungeons.id`） |
| `min_depth` | int unsigned | 不可 | - | 報酬付与対象の到達深度（この深度に到達したら付与） |
| `resource_type` | enum | 不可 | - | 報酬タイプ。`Exp` / `Coin` / `FreeDiamond` / `Item` / `Emblem` / `Unit` |
| `resource_id` | varchar(255) | 許可 | - | 報酬リソースID。`Exp`・`Coin`・`FreeDiamond` の場合はNULL |
| `resource_amount` | int unsigned | 不可 | - | 報酬数量 |
| `sort_order` | int unsigned | 不可 | - | UI表示順序 |

---

## ResourceType（報酬タイプ）

| 値 | 意味 | `resource_id` の必要性 |
|----|------|--------------------|
| `Exp` | 経験値 | NULL（固定リソース） |
| `Coin` | コイン | NULL（固定リソース） |
| `FreeDiamond` | 無料ダイヤ | NULL（固定リソース） |
| `Item` | アイテム | `mst_items.id` を設定 |
| `Emblem` | エンブレム | `mst_emblems.id` を設定 |
| `Unit` | ユニット | `mst_units.id` を設定 |

---

## 他テーブルとの連携

| テーブル | 参照方向 | 用途 |
|---------|---------|------|
| `mst_dungeons` | `mst_dungeon_id` → `id` | 属する開催回の基本設定 |
| `mst_items` | `resource_id` → `id` | `resource_type = Item` の場合に参照 |
| `mst_emblems` | `resource_id` → `id` | `resource_type = Emblem` の場合に参照 |
| `mst_units` | `resource_id` → `id` | `resource_type = Unit` の場合に参照 |

---

## 実データ例

> 2026年3月現在、`MstDungeonDepthReward.csv` は未作成のため実データは存在しない。
> 以下は想定されるデータ形式の例。

### パターン1: 深度10到達時の報酬（コイン + ダイヤ）

```
ENABLE: e
id: dungeon_00001_depth_reward_10_coin
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 10
resource_type: Coin
resource_id: (NULL)
resource_amount: 5000
sort_order: 1

id: dungeon_00001_depth_reward_10_dia
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 10
resource_type: FreeDiamond
resource_id: (NULL)
resource_amount: 50
sort_order: 2
```

深度10到達時にコイン5000枚と無料ダイヤ50個を付与する。同じ深度に複数のレコードを設定することで複数種類の報酬を付与できる。

### パターン2: 深度50到達時の高額報酬（ガチャチケット）

```
ENABLE: e
id: dungeon_00001_depth_reward_50_ticket
release_key: 202601010
mst_dungeon_id: dungeon_00001
min_depth: 50
resource_type: Item
resource_id: item_gacha_ticket_10pull
resource_amount: 1
sort_order: 1
```

深度50到達時に10連ガチャチケット1枚を付与する高価値マイルストーン報酬。

---

## 設定時のポイント

1. **`min_depth` は区間ではなく特定深度への到達時点で付与される**。`MstDungeonDepthSetting` や `MstDungeonCardGroup` の `min_depth`（区間の下限値）とは異なり、この `min_depth` は報酬付与のトリガーとなる深度そのものを示す。

2. **同一 `min_depth` に複数のレコードを設定することで複数種の報酬を一度に付与できる**。`sort_order` で表示順を制御し、視覚的に整理すること。

3. **深度設定はプレイヤーのモチベーション設計と連携する**。序盤の低深度（5, 10, 20など）に報酬を設定することで初心者の継続プレイを促し、高深度（50, 100など）には特別報酬を設定して上級プレイヤーへの目標を提供する。

4. **`resource_type = Item` の場合は `resource_id` に有効なアイテムIDを設定する必須**。NULLにすると報酬付与エラーになる。逆に `Exp`・`Coin`・`FreeDiamond` では `resource_id` を NULL にする。

5. **ダンジョン開催回が変わっても同じ深度構成でよい場合は、`mst_dungeon_id` だけ変えて同様のレコードをコピーする**。深度到達報酬は開催回ごとに独立しているため、毎回設定が必要。

6. **到達報酬は初回到達時のみ付与される**。ゲームリセットや再挑戦での再付与ルールはサーバーロジックで制御されるため、マスタデータとしては「付与する報酬の定義」のみを管理する。
