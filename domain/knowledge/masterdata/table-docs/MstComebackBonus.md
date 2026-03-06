# MstComebackBonus 詳細説明

> CSVパス: `projects/glow-masterdata/MstComebackBonus.csv`

---

## 概要

カムバックボーナスの日別報酬設定を管理するマスタテーブル。
`mst_comeback_bonus_schedules` で定義されたスケジュールに対して、復帰後の何日目にどの報酬グループを受け取るかを定義する。
1つのスケジュールに対してログイン日数分のレコードを作成する（例: 8日間なら8レコード）。

クライアントクラス: `MstComebackBonusData.cs`

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---|---|---|---|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | レコードID（主キー） |
| release_key | bigint | YES | リリースキー（デフォルト: 1） |
| mst_comeback_bonus_schedule_id | varchar(255) | YES | 対応するカムバックボーナススケジュールID（`mst_comeback_bonus_schedules.id`） |
| login_day_count | int unsigned | YES | 条件とするログイン日数（復帰後の何日目か、1始まり） |
| mst_daily_bonus_reward_group_id | varchar(255) | YES | 付与する報酬のグループID（`mst_daily_bonus_rewards.group_id`） |
| sort_order | int unsigned | YES | 表示順（デフォルト: 0） |

ユニークキー: `(mst_comeback_bonus_schedule_id, login_day_count)` の組み合わせで一意となる。

---

## 命名規則 / IDの生成ルール

IDは `comeback_{スケジュール連番}_{日数連番}` の形式が一般的。

例:
- `comeback_1_1` → スケジュール1の1日目
- `comeback_1_7` → スケジュール1の7日目

---

## 他テーブルとの連携

| 参照先テーブル | カラム | 内容 |
|---|---|---|
| `mst_comeback_bonus_schedules` | `mst_comeback_bonus_schedule_id` | 親スケジュールの参照 |
| `mst_daily_bonus_rewards` | `mst_daily_bonus_reward_group_id` | 報酬グループの参照（`group_id` カラムで検索） |

---

## 実データ例

**例1: スケジュール1（comeback_daily_bonus_1）の日別報酬設定（7日分）**

| id | release_key | mst_comeback_bonus_schedule_id | login_day_count | mst_daily_bonus_reward_group_id | sort_order |
|---|---|---|---|---|---|
| comeback_1_1 | 202510010 | comeback_daily_bonus_1 | 1 | comeback_reward_group_1 | 1 |
| comeback_1_2 | 202510010 | comeback_daily_bonus_1 | 2 | comeback_reward_group_2 | 2 |
| comeback_1_3 | 202510010 | comeback_daily_bonus_1 | 3 | comeback_reward_group_3 | 3 |
| comeback_1_4 | 202510010 | comeback_daily_bonus_1 | 4 | comeback_reward_group_4 | 4 |
| comeback_1_5 | 202510010 | comeback_daily_bonus_1 | 5 | comeback_reward_group_5 | 5 |
| comeback_1_6 | 202510010 | comeback_daily_bonus_1 | 6 | comeback_reward_group_6 | 6 |
| comeback_1_7 | 202510010 | comeback_daily_bonus_1 | 7 | comeback_reward_group_7 | 7 |

スケジュールの `duration_days` が8日のため、`login_day_count` 1〜8のレコードを設定する（現在7レコード確認、8日目のレコードも必要）。

---

## 設定時のポイント

1. `login_day_count` は1から始まり、スケジュールの `duration_days` までの全日分のレコードを連続して作成する。
2. `mst_daily_bonus_reward_group_id` には `mst_daily_bonus_rewards.group_id` に存在するグループIDを設定する。
3. `sort_order` は `login_day_count` と同じ値を設定することが一般的（表示順と日数を対応させる）。
4. スケジュール内のレコード数は `mst_comeback_bonus_schedules.duration_days` と一致させること。
5. 報酬はログイン日数が進むほど豪華にする設計が推奨される（後半ほど高価値な報酬）。
6. 新しいカムバックスケジュールを追加する際は、スケジュールID・日数・報酬グループをセットで作成する。
7. 複数のスケジュールが同時期に有効になる場合、どのスケジュールが適用されるかサーバー実装で確認する。
8. `mst_daily_bonus_rewards` テーブルに対応する報酬グループのレコードが作成されていることを必ず事前確認する。
