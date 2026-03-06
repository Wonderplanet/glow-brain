# MstStageReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageReward.csv`

---

## 概要

通常ステージのクリア報酬設定を管理するテーブル。各ステージで獲得できる報酬（初回クリア報酬・ランダムドロップ報酬など）を設定する。イベントクエスト専用の報酬は `mst_stage_event_rewards` を使用するため、このテーブルは主に通常クエスト向け。

- 1ステージにつき複数レコードを設定可能
- `reward_category` で報酬の付与タイミング（初回クリア・毎回・ランダム）を指定
- `percentage` は `Random` カテゴリの出現比重（抽選の重みづけ）に使用
- `sort_order` で UI 上の表示順序を制御

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 連番ID（整数） |
| mst_stage_id | varchar(255) | 不可 | - | 対象ステージID（`mst_stages.id`） |
| reward_category | enum | 不可 | - | 報酬カテゴリ（`RewardCategory` enum参照） |
| resource_type | enum | 不可 | - | 報酬タイプ（`ResourceType` enum参照） |
| resource_id | varchar(255) | 可 | - | 報酬リソースID（Coin/Expなどは NULL） |
| resource_amount | int unsigned | 不可 | - | 報酬数量 |
| percentage | int unsigned | 不可 | - | 出現比重（Randomの抽選重みに使用） |
| sort_order | int unsigned | 不可 | - | 表示順序（昇順） |
| release_key | bigint unsigned | 不可 | `1` | リリースキー |

**インデックス**: `mst_stage_id_index`（`mst_stage_id`）

---

## RewardCategory（報酬カテゴリ）

| 値 | 説明 |
|---|---|
| `Always` | 毎回クリア時に必ず付与 |
| `FirstClear` | 初回クリア時のみ付与 |
| `Random` | percentage の比重に基づいてランダムで付与 |

---

## ResourceType（報酬タイプ）

| 値 | 説明 |
|---|---|
| `Exp` | 経験値（resource_idは NULL） |
| `Coin` | コイン（resource_idは NULL） |
| `FreeDiamond` | 無償ダイヤ（resource_idは NULL） |
| `Item` | アイテム（resource_idでアイテムIDを指定） |
| `Emblem` | エンブレム（resource_idでエンブレムIDを指定） |
| `Unit` | キャラ（resource_idでキャラIDを指定） |

---

## 他テーブルとの連携

| 関連テーブル | カラム | 説明 |
|---|---|---|
| `mst_stages` | `mst_stage_id` → `mst_stages.id` | 対象ステージ |
| `mst_items` | `resource_id` → `mst_items.id`（resource_typeがItemの場合） | 報酬アイテムの詳細 |
| `mst_units` | `resource_id` → `mst_units.id`（resource_typeがUnitの場合） | 報酬キャラの詳細 |

---

## 実データ例

### 例1: 通常ステージの初回クリア報酬 + ランダムドロップ（記憶の欠片系）

```
id | mst_stage_id     | reward_category | resource_type | resource_id              | resource_amount | percentage | sort_order | release_key
1  | normal_spy_00001 | FirstClear      | Item          | memoryfragment_glo_00001 | 1               | 100        | 2          | 202509010
2  | normal_spy_00001 | Random          | Item          | memory_glo_00001         | 5               | 65         | 3          | 202509010
3  | normal_spy_00001 | Random          | Item          | memory_glo_00001         | 8               | 25         | 4          | 202509010
4  | normal_spy_00001 | Random          | Item          | memory_glo_00001         | 12              | 5          | 5          | 202509010
5  | normal_spy_00001 | Random          | Item          | memoryfragment_glo_00001 | 1               | 10         | 6          | 202509010
```

初回クリアで記憶の欠片1個を確定取得。毎回クリアでは比重65:25:5:10のランダムで記憶ポイント5・8・12個か欠片1個が抽選される。

### 例2: 別ステージの初回クリア報酬（インデックス2以降）

```
id | mst_stage_id     | reward_category | resource_type | resource_id              | resource_amount | percentage | sort_order | release_key
6  | normal_spy_00002 | FirstClear      | Item          | memoryfragment_glo_00001 | 1               | 100        | 1          | 202509010
7  | normal_spy_00002 | Random          | Item          | memory_glo_00001         | 5               | 65         | 2          | 202509010
8  | normal_spy_00002 | Random          | Item          | memory_glo_00001         | 8               | 25         | 3          | 202509010
9  | normal_spy_00002 | Random          | Item          | memory_glo_00001         | 12              | 5          | 4          | 202509010
10 | normal_spy_00002 | Random          | Item          | memoryfragment_glo_00001 | 1               | 10         | 5          | 202509010
```

---

## 設定時のポイント

1. **percentage は確率ではなく比重**: `Random` カテゴリの `percentage` は各項目の比重（ウェイト）であり、合計が100%でなくても動作する（相対比で抽選）。ただし実データでは合計を基準に確率を設計している
2. **Always/FirstClear の percentage は 100**: 確定報酬の場合は percentage を 100 に設定する（実際には使用しないが定義として必要）
3. **Coin/Exp/FreeDiamond は resource_id を NULL**: これらは resource_id が不要のため NULL を設定する
4. **sort_order で表示順を管理**: クライアントでの報酬一覧表示はこの order に従う。FirstClear を先に表示する場合は小さい値を割り当てる
5. **イベントクエストには使用しない**: イベントクエストステージの報酬は `mst_stage_event_rewards` を使用する
6. **クライアントクラス**: `MstStageRewardData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstStageId`・`rewardCategory`・`resourceType`・`resourceId`・`resourceAmount`・`percentage`・`sortOrder` が配信される
7. **mst_stage_id にインデックスあり**: 複数件存在するため検索効率のためにインデックスが設定されている
