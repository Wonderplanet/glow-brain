# MstDailyBonusReward 詳細説明

> CSVパス: `projects/glow-masterdata/MstDailyBonusReward.csv`

---

## 概要

ログインボーナス報酬のグループ別設定を管理するマスタテーブル。
報酬をグループID（`group_id`）でまとめることにより、1つのグループに複数種類の報酬を設定できる。

**注意: このテーブルは現在ログインボーナス本体には未使用。** 通常のログインボーナスやイベントログインボーナスは `mst_mission_rewards` テーブルで設定されている。ただし、カムバックボーナス機能（`mst_comeback_bonuses`）からは `group_id` 経由で参照されている。

クライアントクラス: `MstDailyBonusRewardData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| group_id | varchar(255) | YES | 報酬グループID（同一グループを `mst_comeback_bonuses` から参照） |
| resource_type | enum('Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit') | YES | 報酬タイプ |
| resource_id | varchar(255) | NO | 報酬リソースID（タイプによっては不要、NULL可） |
| resource_amount | int unsigned | YES | 報酬数量 |

インデックス: `group_id` にBTREEインデックスあり（グループ検索を高速化）。

---

## ResourceType（resource_type enumの値）

| 値 | 説明 | resource_id |
|---|---|---|
| Exp | 経験値 | 不要（NULL） |
| Coin | コイン | 不要（NULL） |
| FreeDiamond | 無償ダイヤ | 不要（NULL） |
| Item | アイテム | `mst_items.id` |
| Emblem | エンブレム | `mst_emblems.id` |
| Stamina | スタミナ | 不要（NULL） |
| Unit | ユニット | `mst_units.id` |

---

## 命名規則 / IDの生成ルール

レコードIDは `{機能名}_reward_{連番}` または `{機能名}_reward_{グループ連番}_{アイテム連番}` の形式が一般的。
`group_id` は `{機能名}_reward_group_{連番}` の形式。

例（カムバックボーナス用）:
- `comeback_reward_1_1` → カムバックボーナス1の報酬グループ1の1番目
- `comeback_reward_group_1` → グループID

---

## 他テーブルとの連携

| 参照元テーブル | カラム | 内容 |
|---|---|---|
| `mst_comeback_bonuses` | `mst_daily_bonus_reward_group_id` | カムバックボーナスの日別報酬グループを参照 |

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_items` | `resource_id` | アイテム報酬の参照（resource_type=Item時） |
| `mst_emblems` | `resource_id` | エンブレム報酬の参照（resource_type=Emblem時） |
| `mst_units` | `resource_id` | ユニット報酬の参照（resource_type=Unit時） |

---

## 実データ例

**例1: カムバックボーナス1日目の報酬（FreeDiamond 150個）**

| id | release_key | group_id | resource_type | resource_id | resource_amount |
|---|---|---|---|---|---|
| comeback_reward_1_1 | 202510010 | comeback_reward_group_1 | FreeDiamond | （空） | 150 |

**例2: カムバックボーナス報酬グループ（7日分抜粋）**

| id | group_id | resource_type | resource_id | resource_amount |
|---|---|---|---|---|
| comeback_reward_1_1 | comeback_reward_group_1 | FreeDiamond | （空） | 150 |
| comeback_reward_1_2 | comeback_reward_group_2 | Coin | （空） | 5000 |
| comeback_reward_1_3 | comeback_reward_group_3 | FreeDiamond | （空） | 150 |
| comeback_reward_1_4 | comeback_reward_group_4 | Coin | （空） | 10000 |
| comeback_reward_1_5 | comeback_reward_group_5 | FreeDiamond | （空） | 200 |
| comeback_reward_1_6 | comeback_reward_group_6 | Coin | （空） | 15000 |
| comeback_reward_1_7 | comeback_reward_group_7 | Item | ticket_glo_00002 | 5 |

日を重ねるごとに報酬価値が上がる設計になっている。

---

## 設定時のポイント

1. `group_id` でレコードをグループ化できるため、1グループに複数の報酬アイテムを設定する場合は同一 `group_id` で複数レコードを作成する。
2. `resource_type` が `Exp`・`Coin`・`FreeDiamond`・`Stamina` の場合は `resource_id` を空文字またはNULLにする。
3. 通常ログインボーナスやイベントログインボーナスは `mst_mission_rewards` テーブルを使用するため、このテーブルへの追記は不要。
4. カムバックボーナスで使用する場合は、`mst_comeback_bonuses` の `mst_daily_bonus_reward_group_id` にこのテーブルの `group_id` を設定する。
5. 日が進むほど報酬を豪華にする設計が推奨される（モチベーション維持のため）。
6. `resource_amount` は報酬の個数・枚数・量を表す正の整数を設定する。
7. 新しいカムバックスケジュールを追加する場合は、必要な日数分の報酬グループ（`group_id`）とレコードをセットで作成する。
8. `group_id` の命名は `{機能名}_reward_group_{連番}` の形式を統一して使用する。
