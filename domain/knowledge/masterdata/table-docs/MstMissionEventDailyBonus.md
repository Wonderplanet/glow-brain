# MstMissionEventDailyBonus 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionEventDailyBonus.csv`

---

## 1. 概要

`MstMissionEventDailyBonus` は**イベントデイリーボーナス（イベントログインボーナス）の各日の報酬設定テーブル**。`MstMissionEventDailyBonusSchedule` でスケジュール（期間）が決まり、このテーブルで「N日目にログインしたら何をもらえるか」を定義する。

テーブル名が `mst_mission_event_daily_bonuses`（複数形）に対して、CSVファイル名は `MstMissionEventDailyBonus.csv`（単数形・bonuses→bonus）となっている点に注意。

### ゲームプレイへの影響

- イベント期間中のログインボーナスの「何日目」にどの報酬を付与するかを管理する
- `login_day_count` でイベント期間内での累計ログイン日数を指定する（イベント開始日から何日目か）
- `sort_order` で表示順を制御する

### テーブル間の関係

```
MstMissionEventDailyBonusSchedule（期間設定）
  └─ id → MstMissionEventDailyBonus.mst_mission_event_daily_bonus_schedule_id（1:N）

MstMissionEventDailyBonus（各日の報酬）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬内容）

例: event_kai_00001_daily_bonus（スケジュール）
  ├─ login_day_count=1: event_kai_00001_daily_bonus_1（1日目 → ピックアップガシャチケット）
  ├─ login_day_count=2: event_kai_00001_daily_bonus_2（2日目 → コイン）
  └─ ...
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_mission_event_daily_bonus_schedule_id` | varchar(255) | 不可 | - | 対応するスケジュールID |
| `login_day_count` | int unsigned | 不可 | - | ボーナス発生のイベント内累計ログイン日数 |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_mission_reward_groups.id`） |
| `sort_order` | int unsigned | 不可 | 0 | 表示順 |
| `備考` | varchar | 可 | - | CSVのみの運営メモ列（DBには存在しない） |

### ユニーク制約

| インデックス名 | カラム | 説明 |
|---|---|---|
| `uk_schedule_id_login_day_count` | (mst_mission_event_daily_bonus_schedule_id, login_day_count) | 同スケジュール内でログイン日数の重複不可 |

---

## 4. 命名規則 / IDの生成ルール

```
{mst_mission_event_daily_bonus_schedule_id}_{連番}
```

例: `event_kai_00001_daily_bonus_1`、`event_kai_00001_daily_bonus_2`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_event_daily_bonus_schedules` | `mst_mission_event_daily_bonus_schedule_id` | 紐づくスケジュール |
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | 報酬内容 |

---

## 6. 実データ例

### event_kai_00001（古橋 伊春イベント）のログインボーナス報酬

| id | release_key | mst_mission_event_daily_bonus_schedule_id | login_day_count | mst_mission_reward_group_id | sort_order | 備考 |
|---|---|---|---|---|---|---|
| event_kai_00001_daily_bonus_1 | 202509010 | event_kai_00001_daily_bonus | 1 | event_kai_00001_daily_bonus_1 | 1 | ピックアップガシャチケット |
| event_kai_00001_daily_bonus_2 | 202509010 | event_kai_00001_daily_bonus | 2 | event_kai_00001_daily_bonus_2 | 1 | コイン |
| event_kai_00001_daily_bonus_3 | 202509010 | event_kai_00001_daily_bonus | 3 | event_kai_00001_daily_bonus_3 | 1 | プリズム |
| event_kai_00001_daily_bonus_7 | 202509010 | event_kai_00001_daily_bonus | 7 | event_kai_00001_daily_bonus_7 | 1 | メモリーフラグメント・中級 |
| event_kai_00001_daily_bonus_10 | 202509010 | event_kai_00001_daily_bonus | 10 | event_kai_00001_daily_bonus_10 | 1 | プリズム |

- 日数ごとに異なる報酬を設定
- `備考` 列はDBには存在しない。CSV管理用のメモとして記入されている

---

## 7. 設定時のポイント

- 同スケジュール内で `login_day_count` は重複禁止（ユニーク制約）
- `sort_order` は現行データでは全て `1` が設定されている（フロントエンドで `login_day_count` 順に並べているため）
- `備考` 列はDBスキーマに存在しないCSV専用の列。報酬内容の確認メモとして活用する
- `mst_mission_reward_group_id` と `id` の命名を一致させる（例: `event_kai_00001_daily_bonus_1` → report group id も `event_kai_00001_daily_bonus_1`）
- テーブル名（DB: `mst_mission_event_daily_bonuses`）とCSV名（`MstMissionEventDailyBonus.csv`）の命名差異（bonuses→bonus）に注意
- 対応する `MstMissionEventDailyBonusSchedule` レコードが先に作成されていることを確認する
- クライアントクラス: `MstMissionEventDailyBonusData.cs`
