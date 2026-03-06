# MstMissionDailyBonus 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionDailyBonus.csv`

---

## 1. 概要

`MstMissionDailyBonus` は**デイリーボーナス（ログインボーナス）の設定テーブル**。累計ログイン日数に応じてプレイヤーに付与されるログインボーナスの各ステップを定義する。何日ログインしたらどの報酬を受け取れるかを管理する。

テーブル名が `mst_mission_daily_bonuses`（複数形）に対して、CSVファイル名は `MstMissionDailyBonus.csv`（単数形・bonuses→bonus）となっている点に注意。

### ゲームプレイへの影響

- 累計ログイン日数（`login_day_count`）に応じた報酬を定義する
- `mission_daily_bonus_type` で複数のボーナスセットを管理できる（現状は `DailyBonus` のみ）
- `sort_order` で表示順を制御する
- 報酬の内容は `mst_mission_reward_group_id` で `MstMissionReward` テーブルと紐づく

### テーブル間の関係

```
MstMissionDailyBonus（ログインボーナス設定）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）

login_day_count=1 → 1日目報酬
login_day_count=2 → 2日目報酬
login_day_count=7 → 7日目（週完走）報酬
```

---

## 2. 全カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mission_daily_bonus_type` | enum('DailyBonus') | 不可 | - | ボーナスタイプ（現状 `DailyBonus` のみ） |
| `login_day_count` | int unsigned | 不可 | - | ボーナスが発生する累計ログイン日数 |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_mission_reward_groups.id`） |
| `sort_order` | int unsigned | 不可 | 0 | 表示順 |

### ユニーク制約

| インデックス名 | カラム | 説明 |
|---|---|---|
| `uk_type_login_day_count` | (mission_daily_bonus_type, login_day_count) | 同タイプ内でログイン日数の重複不可 |

---

## 3. MissionDailyBonusType（ボーナスタイプ）

| 値 | 説明 |
|---|---|
| `DailyBonus` | 通常ログインボーナス（累計ログイン日数に応じた報酬） |

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | 各日の報酬内容 |

---

## 6. 実データ例

### 現行のログインボーナス設定

| id | release_key | mission_daily_bonus_type | login_day_count | mst_mission_reward_group_id | sort_order |
|---|---|---|---|---|---|
| daily_bonus_1 | 202509010 | DailyBonus | 1 | daily_bonus_reward_1_1 | 1 |
| daily_bonus_2 | 202509010 | DailyBonus | 2 | daily_bonus_reward_1_2 | 2 |
| daily_bonus_3 | 202509010 | DailyBonus | 3 | daily_bonus_reward_1_3 | 3 |
| daily_bonus_4 | 202509010 | DailyBonus | 4 | daily_bonus_reward_1_4 | 4 |
| daily_bonus_5 | 202509010 | DailyBonus | 5 | daily_bonus_reward_1_5 | 5 |
| daily_bonus_6 | 202509010 | DailyBonus | 6 | daily_bonus_reward_1_6 | 6 |
| daily_bonus_7 | 202509010 | DailyBonus | 7 | daily_bonus_reward_1_7 | 7 |

- 7日分（1週間）のログインボーナスを設定
- `login_day_count` と `sort_order` が対応（1日目=表示順1位）

---

## 7. 設定時のポイント

- `login_day_count` と `mission_daily_bonus_type` の組み合わせがユニークなため、同じタイプで同じログイン日数を重複して設定できない
- 現行は7日サイクルで設定されているが、ログイン日数は累計なので7日以降も `login_day_count = 8` 以降を追加することで継続した報酬設定が可能
- ログインボーナスの報酬内容は `mst_mission_reward_group_id` 経由で `MstMissionReward` に設定する
- `sort_order` は UI での表示順に直接影響するため、`login_day_count` と同じ順序で設定するのが直感的
- テーブル名（DB: `mst_mission_daily_bonuses`）とCSV名（`MstMissionDailyBonus.csv`）の命名差異（bonuses→bonus）に注意
- クライアントクラス: `MstMissionDailyBonusData.cs`
