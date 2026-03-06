# MstMissionDaily 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionDaily.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionDailyI18n.csv`

---

## 1. 概要

`MstMissionDaily` は**デイリーミッションの定義テーブル**。毎日リセットされるミッション課題を設定する。プレイヤーは各ミッションを達成することでボーナスポイントを獲得し、累積ポイントが閾値に達するとボーナス報酬を受け取れる。

`MstMissionDailyI18n` はミッション説明文の多言語データを保持するサブテーブル。

### ゲームプレイへの影響

- デイリーミッションは毎日リセットされ、プレイヤーの日常的なモチベーション維持に使われる
- 2種類のレコードが存在する:
  1. **通常ミッション**: `criterion_type` が具体的なアクション（LoginCount、CoinCollect など）で `bonus_point` が設定される
  2. **ボーナスポイントミッション**: `criterion_type = MissionBonusPoint` で累積ポイントが閾値に達したときの報酬ミッション
- `group_key` でその日のミッション群をグループ化する（例: `Daily1`）
- `destination_scene` でミッション達成後の遷移先を制御する

### テーブル間の関係

```
MstMissionDaily（デイリーミッション本体）
  ├─ id → MstMissionDailyI18n.mst_mission_daily_id（1:N、多言語説明）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）

通常ミッション（daily_2_1〜daily_2_6）→ bonus_point 獲得
ボーナスポイントミッション（daily_bonus_point_2_1〜）→ 累積ポイント達成で報酬
```

---

## 2. 全カラム一覧

### mst_mission_dailies（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値（ガチャIDなど） |
| `criterion_count` | bigint unsigned | 不可 | 0 | 達成に必要な回数・数量 |
| `group_key` | varchar(255) | 可 | - | 分類キー（ミッショングループ識別子） |
| `bonus_point` | bigint unsigned | 不可 | 0 | 達成で得られるボーナスポイント（ボーナスポイントミッションは0） |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_mission_reward_groups.group_id`）。通常ミッションは NULL |
| `sort_order` | int unsigned | 不可 | - | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |

### MstMissionDailyI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_daily_id` | varchar(255) | 不可 | - | 対応するデイリーミッションID |
| `language` | enum('ja') | 不可 | - | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |

---

## 4. 命名規則 / IDの生成ルール

### 通常ミッション

```
daily_{バージョン}_{連番}
```

例: `daily_2_1`、`daily_2_2`

### ボーナスポイントミッション

```
daily_bonus_point_{バージョン}_{連番}
```

例: `daily_bonus_point_2_1`、`daily_bonus_point_2_2`

### i18nテーブル

```
{daily_id}_{言語コード}
```

例: `daily_2_1_ja`、`daily_bonus_point_2_1_ja`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | ボーナスポイントミッションの報酬 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_dailies_i18n` | `mst_mission_daily_id → mst_mission_dailies.id` | 多言語説明文 |

---

## 6. 実データ例

### パターン1: 通常デイリーミッション

| id | criterion_type | criterion_value | criterion_count | group_key | bonus_point | mst_mission_reward_group_id | sort_order | destination_scene |
|---|---|---|---|---|---|---|---|---|
| daily_2_1 | LoginCount | NULL | 1 | Daily1 | 20 | NULL | 1 | Home |
| daily_2_2 | CoinCollect | NULL | 2000 | Daily1 | 20 | NULL | 2 | StageSelect |
| daily_2_3 | IdleIncentiveCount | NULL | 1 | Daily1 | 20 | NULL | 3 | IdleIncentive |
| daily_2_5 | PvpChallengeCount | NULL | 1 | Daily1 | 20 | NULL | 5 | Pvp |
| daily_2_6 | SpecificGachaDrawCount | Special_001 | 1 | Daily1 | 20 | NULL | 6 | Gacha |

- 通常ミッションは `bonus_point` を持ち、`mst_mission_reward_group_id` は NULL

### パターン2: ボーナスポイントミッション（累積ポイント閾値）

| id | criterion_type | criterion_count | group_key | bonus_point | mst_mission_reward_group_id | sort_order |
|---|---|---|---|---|---|---|
| daily_bonus_point_2_1 | MissionBonusPoint | 20 | NULL | 0 | daily_reward_2_1 | 10 |
| daily_bonus_point_2_2 | MissionBonusPoint | 40 | NULL | 0 | daily_reward_2_2 | 11 |
| daily_bonus_point_2_3 | MissionBonusPoint | 60 | NULL | 0 | daily_reward_2_3 | 12 |
| daily_bonus_point_2_4 | MissionBonusPoint | 80 | NULL | 0 | daily_reward_2_4 | 13 |

- ボーナスポイントが 20、40、60、80 に達すると報酬が解放される

### i18n説明文例

| id | mst_mission_daily_id | language | description |
|---|---|---|---|
| daily_2_1_ja | daily_2_1 | ja | ログインしよう |
| daily_2_2_ja | daily_2_2 | ja | コインを累計2,000枚集めよう |
| daily_2_6_ja | daily_2_6 | ja | スペシャルガシャを累計1回引こう |
| daily_bonus_point_2_1_ja | daily_bonus_point_2_1 | ja | 累計ポイントを20貯めよう |

---

## 7. 設定時のポイント

- 通常ミッション（アクション系）は `bonus_point` を設定し、`mst_mission_reward_group_id` は NULL にする
- ボーナスポイントミッションは `criterion_type = MissionBonusPoint`、`bonus_point = 0`、`mst_mission_reward_group_id` を設定する
- 通常ミッションの `bonus_point` 合計がボーナスポイントミッションの最高閾値と一致するよう設定する（例: 通常6ミッション×20pt=120pt、閾値は20、40、60、80、100、120）
- `SpecificGachaDrawCount` などガチャ関連ミッションは `criterion_value` にガチャIDを指定する
- `group_key` は通常ミッションのみ設定し（例: `Daily1`）、ボーナスポイントミッションには設定しない
- 本体レコード追加時は必ず対応する i18n レコード（言語 `ja`）も追加する
- クライアントクラス: `MstMissionDailyData.cs` / `MstMissionDailyI18nData.cs`（`MissionCriterionType` enum を使用）
