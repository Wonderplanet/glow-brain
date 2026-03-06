# MstMissionEventDaily 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionEventDaily.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionEventDailyI18n.csv`

---

## 1. 概要

`MstMissionEventDaily` は**イベントデイリーミッションの定義テーブル**。特定イベント（`mst_event_id`）に紐づき、イベント期間中に毎日リセットされるデイリーミッションを設定する。

`MstMissionEventDailyI18n` は多言語説明文のサブテーブル。

### ゲームプレイへの影響

- イベント期間中のみ有効なデイリーミッションを提供する
- 通常のデイリーミッション（`MstMissionDaily`）とは別枠で提供されるイベント専用のデイリーミッション
- 毎日リセットされ、プレイヤーのイベント期間中の継続モチベーションを高める
- `group_key` でミッション群をグループ化し、進捗管理に使用する

### テーブル間の関係

```
MstEvent（イベント定義）
  └─ mst_event_id → MstMissionEventDaily.mst_event_id（1:N）

MstMissionEventDaily（イベントデイリーミッション本体）
  ├─ id → MstMissionEventDailyI18n.mst_mission_event_daily_id（1:N、多言語）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）
```

---

## 2. 全カラム一覧

### mst_mission_event_dailies（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_event_id` | varchar(255) | 不可 | - | 紐づくイベントID |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値 |
| `criterion_count` | bigint unsigned | 不可 | - | 達成に必要な回数・数量 |
| `group_key` | varchar(255) | 可 | - | 分類キー |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID |
| `sort_order` | int unsigned | 不可 | - | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |

### MstMissionEventDailyI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_event_daily_id` | varchar(255) | 不可 | - | 対応するイベントデイリーミッションID |
| `language` | enum('ja') | 不可 | `ja` | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_events` | `mst_event_id → mst_events.id` | 紐づくイベント |
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | 報酬定義 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_event_dailies_i18n` | `mst_mission_event_daily_id → mst_mission_event_dailies.id` | 多言語説明文 |

---

## 6. 実データ例

### 現行データ

現行の `MstMissionEventDaily.csv` および `MstMissionEventDailyI18n.csv` はデータが 0 件（ヘッダーのみ）の状態。

```
ENABLE, id, release_key, mst_event_id, criterion_type, criterion_value, criterion_count,
group_key, mst_mission_reward_group_id, sort_order, destination_scene
```

イベントデイリーミッション機能は実装済みだが、現時点ではイベントデイリーミッションを持つイベントは設定されていない状態。

---

## 7. 設定時のポイント

- このテーブルは `MstMissionEvent`（イベントミッション・通常版）と構造が似ているが、`unlock_criterion_type` 系のカラムが存在しない（デイリーは毎日リセットされるため開放順制御が不要）
- `MstMissionEventDaily` を追加する際は必ず `mst_event_id` に有効なイベントIDを設定する
- 本体レコード追加時は対応する i18n レコード（言語 `ja`）を必ず追加する
- `mst_mission_event_dailies_i18n` テーブルの外部キー `mst_mission_event_daily_id` はDBスキーマコメントが「mst_mission_events.id」になっているが、正しくは「mst_mission_event_dailies.id」を参照することに注意
- クライアントクラス: `MstMissionEventDailyData.cs`（`MissionCriterionType` enum を使用）
