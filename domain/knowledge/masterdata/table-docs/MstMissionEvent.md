# MstMissionEvent 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionEvent.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionEventI18n.csv`

---

## 1. 概要

`MstMissionEvent` は**イベントミッションの定義テーブル**。特定イベント（`mst_event_id`）に紐づいたミッション課題を設定する。イベント期間中のみ有効で、達成条件・開放条件・報酬・遷移先を管理する。

`MstMissionEventI18n` はミッション説明文の多言語データを保持するサブテーブル。

### ゲームプレイへの影響

- イベントごとに専用ミッションを設定でき、そのイベント期間中のみ有効になる
- `criterion_type` / `criterion_count` で達成条件を定義（例: 特定キャラのグレードを上げる）
- `criterion_value` で対象キャラ・アイテムなどを特定できる（例: `chara_kai_00601`）
- `unlock_criterion_type` / `unlock_criterion_count` でミッションの段階的開放を制御できる
- `event_category` でイベントの種別（`AdventBattle` など）を設定できる
- `mst_mission_event_dependencies` テーブルと組み合わせることで順番に開放されるミッションを設定できる

### テーブル間の関係

```
MstEvent（イベント定義）
  └─ mst_event_id → MstMissionEvent.mst_event_id（1:N）

MstMissionEvent（イベントミッション本体）
  ├─ id → MstMissionEventI18n.mst_mission_event_id（1:N、多言語）
  ├─ id → MstMissionEventDependency.mst_mission_event_id（開放順制御）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）
```

---

## 2. 全カラム一覧

### mst_mission_events（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `mst_event_id` | varchar(255) | 不可 | - | 紐づくイベントID |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値（キャラIDなど） |
| `criterion_count` | bigint unsigned | 不可 | - | 達成に必要な回数・数量 |
| `unlock_criterion_type` | varchar(255) | 可 | - | 開放条件タイプ（`__NULL__` = 常時表示） |
| `unlock_criterion_value` | varchar(255) | 可 | - | 開放条件の補助値 |
| `unlock_criterion_count` | bigint unsigned | 不可 | - | 開放条件の達成回数 |
| `group_key` | varchar(255) | 可 | - | 分類キー |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID |
| `event_category` | enum('AdventBattle') | 可 | - | イベントカテゴリー |
| `sort_order` | int unsigned | 不可 | - | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |

### MstMissionEventI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_event_id` | varchar(255) | 不可 | - | 対応するイベントミッションID |
| `language` | enum('ja') | 不可 | `ja` | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |

---

## 3. EventCategory（イベントカテゴリー）

| 値 | 説明 |
|---|---|
| `AdventBattle` | 降臨バトル系イベントのミッション |
| NULL | 通常イベントミッション |

---

## 4. 命名規則 / IDの生成ルール

### 本体テーブル（MstMissionEvent）

```
{mst_event_id}_{連番}
```

例: `event_kai_00001_1`、`event_kai_00001_2`

### i18nテーブル（MstMissionEventI18n）

```
{event_mission_id}_{言語コード}
```

例: `event_kai_00001_1_ja`

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
| `mst_mission_events_i18n` | `mst_mission_event_id → mst_mission_events.id` | 多言語説明文 |
| `mst_mission_event_dependencies` | `mst_mission_event_id → mst_mission_events.id` | 開放順制御 |

---

## 6. 実データ例

### パターン1: キャラ育成系イベントミッション

| id | mst_event_id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | mst_mission_reward_group_id | sort_order | destination_scene |
|---|---|---|---|---|---|---|---|---|
| event_kai_00001_1 | event_kai_00001 | SpecificUnitGradeUpCount | chara_kai_00601 | 2 | __NULL__ | kai_00001_event_reward_01 | 1 | UnitList |
| event_kai_00001_2 | event_kai_00001 | SpecificUnitGradeUpCount | chara_kai_00601 | 3 | __NULL__ | kai_00001_event_reward_02 | 2 | UnitList |
| event_kai_00001_5 | event_kai_00001 | SpecificUnitLevel | chara_kai_00601 | 20 | __NULL__ | kai_00001_event_reward_05 | 5 | UnitList |

- `criterion_value` に対象キャラID（`chara_kai_00601`）を指定してキャラ固有の育成ミッションを設定
- `SpecificUnitGradeUpCount`（グレードアップ回数）と `SpecificUnitLevel`（レベル）の組み合わせ

### パターン2: i18n説明文例

| id | mst_mission_event_id | language | description |
|---|---|---|---|
| event_kai_00001_1_ja | event_kai_00001_1 | ja | 古橋 伊春 をグレード2まで強化しよう |
| event_kai_00001_5_ja | event_kai_00001_5 | ja | 古橋 伊春 をLv.20まで強化しよう |
| event_kai_00001_8_ja | event_kai_00001_8 | ja | 四ノ宮 功 をグレード2まで強化しよう |

---

## 7. 設定時のポイント

- `mst_event_id` はイベントテーブルの有効なIDと一致させる
- `criterion_value` でキャラやアイテムを特定する条件タイプ（`SpecificUnitGradeUpCount` など）では対象IDを正確に設定する
- `unlock_criterion_type` の `__NULL__`（文字列）は常時表示を意味し、NULL とは区別する
- 開放順制御が必要な場合は `MstMissionEventDependency` と組み合わせる
- イベントミッションの `release_key` はそのイベントのリリースキーと統一する
- 本体レコード追加時は対応する i18n レコード（言語 `ja`）を必ず追加する
- `event_category` は降臨バトル系イベントの場合のみ `AdventBattle` を設定する。通常イベントは NULL
- クライアントクラス: `MstMissionEventData.cs` / `MstMissionEventI18nData.cs`（`MissionCriterionType`、`EventCategory` enum を使用）
