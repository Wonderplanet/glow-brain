# MstMissionEventDailyBonusSchedule 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionEventDailyBonusSchedule.csv`

---

## 1. 概要

`MstMissionEventDailyBonusSchedule` は**イベントデイリーボーナス（イベントログインボーナス）のスケジュール設定テーブル**。各イベントのログインボーナス期間（開始日時・終了日時）を管理する。実際の報酬内容は `MstMissionEventDailyBonus` テーブルで定義される。

### ゲームプレイへの影響

- イベント期間中に設定されるログインボーナスの「いつからいつまで」の期間を制御する
- 1レコード = 1イベントのログインボーナス期間
- この期間内にログインすると `MstMissionEventDailyBonus` の `login_day_count` に応じた報酬を受け取れる

### テーブル間の関係

```
MstEvent（イベント定義）
  └─ mst_event_id → MstMissionEventDailyBonusSchedule.mst_event_id（1:1）

MstMissionEventDailyBonusSchedule（スケジュール）
  └─ id → MstMissionEventDailyBonus.mst_mission_event_daily_bonus_schedule_id（1:N、各日の報酬）
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `mst_event_id` | varchar(255) | 不可 | - | 紐づくイベントID（`mst_events.id`） |
| `start_at` | timestamp | 不可 | - | ログインボーナス開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | ログインボーナス終了日時（UTC） |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 4. 命名規則 / IDの生成ルール

```
{mst_event_id}_daily_bonus
```

例: `event_kai_00001_daily_bonus`、`event_spy_00001_daily_bonus`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_events` | `mst_event_id → mst_events.id` | 紐づくイベント |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_event_daily_bonuses` | `mst_mission_event_daily_bonus_schedule_id → mst_mission_event_daily_bonus_schedules.id` | 各日の報酬設定 |

---

## 6. 実データ例

### イベントログインボーナスのスケジュール

| id | release_key | mst_event_id | start_at | end_at |
|---|---|---|---|---|
| event_kai_00001_daily_bonus | 202509010 | event_kai_00001 | 2025-09-24 14:00:00 | 2025-10-06 03:59:59 |
| event_spy_00001_daily_bonus | 202510010 | event_spy_00001 | 2025-10-06 15:00:00 | 2025-10-22 03:59:59 |
| event_dan_00001_daily_bonus | 202510020 | event_dan_00001 | 2025-10-22 15:00:00 | 2025-11-06 03:59:59 |
| event_kim_00001_daily_bonus | 202602020 | event_kim_00001 | 2026-02-16 15:00:00 | 2026-03-02 03:59:59 |

- 各イベントに対してログインボーナス期間が設定される
- 時刻は UTC（日本時間から9時間引いた値）で設定

---

## 7. 設定時のポイント

- `start_at` / `end_at` は UTC タイムスタンプで設定する（JST 15:00 → UTC 06:00 / JST 24:00 → UTC 15:00）
- `end_at` は通常イベント終了時刻（`mst_events` テーブルの終了日時）と合わせて設定する
- ID命名は `{mst_event_id}_daily_bonus` の規則に従う
- `release_key` はそのイベントのリリースキーと統一する
- このテーブルに加えて `MstMissionEventDailyBonus` に各日（`login_day_count` 1日目〜N日目）の報酬も設定する必要がある
- イベント期間が変更になった場合は `start_at` / `end_at` を更新する
