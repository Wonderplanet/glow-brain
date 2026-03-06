# MstMissionReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionReward.csv`

---

## 概要

`MstMissionReward` は**ミッション達成時の報酬を定義するテーブル**。ウィークリーミッションやデイリーボーナスなど、さまざまなミッションの報酬アイテム・通貨を `group_id` でグルーピングして管理する。

1つの `group_id` に対して複数のレコードを紐付けることで、複数種類のリソースをまとめて1つの「報酬セット」として扱える。`MstMissionWeekly` の `mst_mission_reward_group_id` カラムがこのテーブルの `group_id` を参照する形で連携する。

### ゲームプレイへの影響

- ミッション達成時に `group_id` に紐付くすべてのレコードがまとめて付与される
- `resource_type` によって付与される通貨・アイテムの種類が変わる（無償ダイヤ、コイン、アイテムなど）
- `resource_id` は `resource_type` が `Item` / `Emblem` / `Unit` / `ArtworkFragment` の場合のみ使用し、`FreeDiamond` / `Coin` / `Exp` の場合は NULL

---

## 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。UUID形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー。マスタデータのバージョン管理に使用 |
| `group_id` | varchar(255) | 不可 | - | 報酬グルーピングID。複数レコードをまとめる識別子 |
| `resource_type` | enum | 不可 | - | 報酬タイプ（後述のenum参照） |
| `resource_id` | varchar(255) | 可 | NULL | 報酬リソースID。resource_typeがItem等の場合に使用 |
| `resource_amount` | int unsigned | 不可 | 0 | 報酬の個数・数量 |
| `sort_order` | int unsigned | 不可 | - | 並び順 |

---

## ResourceType（報酬タイプ）

| 値 | 説明 | resource_id |
|----|------|------------|
| `Exp` | 経験値 | NULL |
| `Coin` | コイン | NULL |
| `FreeDiamond` | 無償ダイヤ | NULL |
| `Item` | アイテム | アイテムID |
| `Emblem` | エンブレム | エンブレムID |
| `Unit` | ユニット（キャラ） | ユニットID |
| `ArtworkFragment` | アートワークのかけら | アートワークIDなど |

---

## 命名規則 / IDの生成ルール

- `id`: `mission_reward_{連番}` 形式（例: `mission_reward_1`、`mission_reward_10`）
- `group_id`: 用途に応じて自由に命名。ミッション種別や番号を含める形が多い
  - デイリーボーナス系: `daily_bonus_reward_{連番}_{日数}` 形式
  - ウィークリー報酬系: `weekly_reward_{連番}_{順番}` 形式
  - イベント報酬系: `{イベントID}_event_reward_{順番}` 形式

---

## 他テーブルとの連携

```
MstMissionWeekly
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（1グループ = 複数レコード）

MstMissionReward
  └─ resource_id → MstItem.id（resource_type = Item の場合）
  └─ resource_id → MstUnit.id（resource_type = Unit の場合）
```

---

## 実データ例

**パターン1: 無償ダイヤ報酬**

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order |
|--------|-----|-------------|----------|---------------|-------------|-----------------|------------|
| e | mission_reward_1 | 202509010 | daily_bonus_reward_1_1 | FreeDiamond | NULL | 20 | 1 |
| e | mission_reward_2 | 202509010 | daily_bonus_reward_1_2 | Coin | NULL | 2000 | 1 |

**パターン2: 複数リソースの報酬セット（1つのgroup_idに複数レコード）**

| ENABLE | id | release_key | group_id | resource_type | resource_id | resource_amount | sort_order |
|--------|-----|-------------|----------|---------------|-------------|-----------------|------------|
| e | mission_reward_7 | 202509010 | daily_bonus_reward_1_7 | FreeDiamond | NULL | 50 | 1 |
| e | mission_reward_8 | 202509010 | daily_bonus_reward_1_7 | Item | entry_item_glo_00001 | 1 | 1 |
| e | mission_reward_9 | 202509010 | daily_bonus_reward_1_7 | Item | memoryfragment_glo_00001 | 5 | 1 |

---

## 設定時のポイント

1. **group_idで報酬セットを管理**: 1つの `group_id` に複数レコードを紐付けることで「ダイヤ50個 + アイテム1個」のような複合報酬が実現できる
2. **resource_idの使い分け**: `FreeDiamond`・`Coin`・`Exp` は resource_id を NULL にする。`Item`・`Emblem`・`Unit`・`ArtworkFragment` は対応するマスタのIDを設定する
3. **group_idはMstMissionWeeklyと一致させる**: `MstMissionWeekly.mst_mission_reward_group_id` に設定するgroup_idとこのテーブルの `group_id` を一致させないと報酬が付与されない
4. **sort_orderは表示順**: 複数報酬がある場合にUI上での並び順を制御する
5. **resource_amountのデフォルトは0**: 設定忘れを防ぐために必ず正の値を設定する
6. **イベント報酬の命名**: イベントIDをprefixとして含めると管理しやすい（例: `kai_00001_event_reward_01`）
