# MstStageEventReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstStageEventReward.csv`

---

## 概要

イベントクエストステージのクリア報酬を管理するテーブル。通常クエストの報酬（`mst_stage_rewards`）とは別に、イベントクエスト専用の報酬設定を行う。報酬カテゴリ（初回クリア報酬・毎回報酬・ランダム報酬）と報酬タイプ・数量を組み合わせて設定する。

- 1ステージにつき複数レコード設定可能
- `reward_category` で「初回クリア時のみ」「毎回」「ランダム抽選」の3種類を設定
- `percentage` はカテゴリが `Random` のときに使用するドロップ確率（パーセント）
- `sort_order` で報酬の表示順序を制御

---

## 全カラム一覧

| カラム名 | 型 | NULL | デフォルト | 説明 |
|---|---|---|---|---|
| id | varchar(255) | 不可 | - | 連番ID（整数） |
| mst_stage_id | varchar(255) | 不可 | - | 対象ステージID（`mst_stages.id`） |
| reward_category | enum | 不可 | - | 報酬カテゴリ（`RewardCategory` enum参照） |
| resource_type | enum | 不可 | - | 報酬タイプ（`ResourceType` enum参照） |
| resource_id | varchar(255) | 可 | - | 報酬リソースID（Coin/Expは NULL） |
| resource_amount | int unsigned | 不可 | - | 報酬数量 |
| percentage | int unsigned | 不可 | - | ドロップ確率（Random時に使用、%指定） |
| sort_order | int unsigned | 不可 | - | 報酬の表示順序（昇順） |
| release_key | bigint | 不可 | - | リリースキー |

---

## RewardCategory（報酬カテゴリ）

| 値 | 説明 |
|---|---|
| `Always` | 毎回クリア時に必ず付与される報酬 |
| `FirstClear` | 初回クリア時のみ付与される報酬 |
| `Random` | percentage（%）の確率でランダムに付与される報酬 |

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
| `mst_stages` | `mst_stage_id` → `mst_stages.id` | イベントクエストステージの特定 |
| `mst_items` | `resource_id` → `mst_items.id`（resource_typeがItemの場合） | 報酬アイテムの詳細 |
| `mst_units` | `resource_id` → `mst_units.id`（resource_typeがUnitの場合） | 報酬キャラの詳細 |
| `mst_stage_event_settings` | `mst_stage_id` | イベントステージの開始・終了日時などの設定 |

---

## 実データ例

### 例1: イベントステージの初回クリア報酬（キャラ獲得ステージ）

```
id | mst_stage_id                | reward_category | resource_type | resource_id     | resource_amount | percentage | sort_order | release_key
2  | event_kai1_charaget01_00001 | FirstClear      | Unit          | chara_kai_00601 | 1               | 100        | 1          | 202509010
3  | event_kai1_charaget01_00001 | FirstClear      | FreeDiamond   | prism_glo_00001 | 30              | 100        | 2          | 202509010
4  | event_kai1_charaget01_00001 | FirstClear      | Coin          | NULL            | 250             | 100        | 3          | 202509010
5  | event_kai1_charaget01_00001 | Random          | Item          | piece_kai_00601 | 3               | 10         | 4          | 202509010
```

初回クリアでキャラ・ダイヤ・コインを確定取得し、追加で10%の確率でキャラのかけらが3個ドロップ。

### 例2: 1日クリア可能なイベントステージ

```
id | mst_stage_id           | reward_category | resource_type | resource_id     | resource_amount | percentage | sort_order | release_key
1  | event_kai1_1day_00001  | FirstClear      | FreeDiamond   | prism_glo_00001 | 20              | 100        | 1          | 202509010
```

初回クリアのみ無償ダイヤ20個を付与。

---

## 設定時のポイント

1. **Always/FirstClear の percentage は 100 を設定**: 確定報酬の場合は percentage を 100 にする（確率値としては意味を持たないが、フィールドには値が必要）
2. **Random の percentage はドロップ確率（%）**: 例えば10%なら `10`、100%確定なら `100` を設定する
3. **sort_order で表示順を制御**: UI上での報酬表示順序を sort_order 昇順で整理する。FirstClear 報酬を先頭に設定することが多い
4. **Coin/Exp/FreeDiamond は resource_id を NULL**: これらのリソースタイプはIDが不要のため NULL にする
5. **mst_stage_event_settings との対応**: イベントステージには必ず対応する `mst_stage_event_settings` レコードも設定する
6. **クライアントクラス**: `MstStageEventRewardData`（`GLOW.Core.Data.Data`名前空間）。`id`・`mstStageId`・`rewardCategory`・`resourceType`・`resourceId`・`resourceAmount`・`percentage`・`sortOrder` が配信される
7. **通常クエスト報酬との使い分け**: 通常クエストは `mst_stage_rewards` を使用し、イベントクエストのみこのテーブルを使用する
