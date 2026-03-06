# MstMissionWeekly 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionWeekly.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionWeeklyI18n.csv`

---

## 概要

`MstMissionWeekly` は**ウィークリーミッションの設定テーブル**。毎週プレイヤーに提示されるミッションの達成条件・ボーナスポイント・報酬などを定義する。

ミッションには2種類のレコードがある:
1. **通常ミッション** (`group_key` あり): プレイヤーが達成してボーナスポイントを獲得するもの（ログイン、探索、PvP、コイン収集など）
2. **ボーナスポイント累計ミッション** (`criterion_type = MissionBonusPoint`): 通常ミッションで貯めたボーナスポイントが一定量に達すると報酬が付与されるもの

`MstMissionWeeklyI18n` テーブルと連携してミッションの説明文を多言語対応する。

### ゲームプレイへの影響

- `criterion_type` と `criterion_count` の組み合わせで達成条件を決定する
- `bonus_point` を蓄積し、`MissionBonusPoint` タイプのミッションで累計報酬がアンロックされる
- `destination_scene` でミッションタップ時の遷移先画面を制御する

---

## 全カラム一覧

### mst_mission_weeklies カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー。UUID形式 |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ（後述参照） |
| `criterion_value` | varchar(255) | 可 | NULL | 達成条件値（条件タイプによっては使用しない） |
| `criterion_count` | bigint unsigned | 不可 | 0 | 達成に必要な回数・数量 |
| `group_key` | varchar(255) | 可 | NULL | ミッションのグループ分類キー（例: `Weekly2`） |
| `bonus_point` | bigint unsigned | 不可 | 0 | ミッション達成で得られるボーナスポイント量 |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 参照先報酬グループID（`mst_mission_rewards.group_id`） |
| `sort_order` | int unsigned | 不可 | - | 並び順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッションタップ時の遷移先画面名 |

### MstMissionWeeklyI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|---------|-----------|----|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_weekly_id` | varchar(255) | 不可 | - | 親テーブルID（`mst_mission_weeklies.id`） |
| `language` | enum('ja') | 不可 | - | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## criterion_type（達成条件タイプ）

| 値 | 説明 | criterion_value | criterion_count |
|----|------|----------------|----------------|
| `LoginCount` | ログイン回数 | NULL | ログイン日数 |
| `IdleIncentiveCount` | 探索報酬受け取り回数 | NULL | 受け取り回数 |
| `PvpChallengeCount` | PvP挑戦回数 | NULL | 挑戦回数 |
| `CoinCollect` | コイン収集量 | NULL | 収集枚数 |
| `MissionBonusPoint` | ミッションボーナスポイント累計 | NULL | ポイント累計量 |

---

## 命名規則 / IDの生成ルール

- `id`: ミッション種別+連番形式
  - 通常ミッション: `weekly_{グループ番号}_{順番}` （例: `weekly_2_1`）
  - ボーナスポイント累計: `weekly_bonus_point_{グループ番号}_{順番}` （例: `weekly_bonus_point_2_1`）
- `MstMissionWeeklyI18n.id`: `{mst_mission_weekly_id}_{言語コード}` （例: `weekly_2_1_ja`）
- `group_key`: `Weekly{グループ番号}` （例: `Weekly2`）

---

## 他テーブルとの連携

```
MstMissionWeekly
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（達成時の報酬）
  └─ id → MstMissionWeeklyI18n.mst_mission_weekly_id（多言語説明文）

MstMissionWeekly（MissionBonusPointタイプ）
  └─ criterion_count = ポイント累計しきい値（通常ミッションのbonus_pointの合計で達成）
```

---

## 実データ例

**パターン1: 通常ウィークリーミッション**

| ENABLE | id | release_key | criterion_type | criterion_value | criterion_count | group_key | bonus_point | mst_mission_reward_group_id | sort_order | destination_scene |
|--------|-----|-------------|---------------|----------------|----------------|-----------|-------------|----------------------------|------------|-------------------|
| e | weekly_2_1 | 202509010 | LoginCount | NULL | 3 | Weekly2 | 20 | NULL | 1 | Home |
| e | weekly_2_5 | 202509010 | PvpChallengeCount | NULL | 5 | Weekly2 | 20 | NULL | 5 | Pvp |

**パターン2: ボーナスポイント累計ミッション**

| ENABLE | id | release_key | criterion_type | criterion_value | criterion_count | group_key | bonus_point | mst_mission_reward_group_id | sort_order | destination_scene |
|--------|-----|-------------|---------------|----------------|----------------|-----------|-------------|----------------------------|------------|-------------------|
| e | weekly_bonus_point_2_1 | 202509010 | MissionBonusPoint | NULL | 20 | NULL | NULL | weekly_reward_2_1 | 10 | NULL |
| e | weekly_bonus_point_2_2 | 202509010 | MissionBonusPoint | NULL | 40 | NULL | NULL | weekly_reward_2_2 | 11 | NULL |

**I18nデータ例**

| ENABLE | release_key | id | mst_mission_weekly_id | language | description |
|--------|-------------|-----|----------------------|----------|-------------|
| e | 202509010 | weekly_2_1_ja | weekly_2_1 | ja | 3日ログインしよう |
| e | 202509010 | weekly_2_5_ja | weekly_2_5 | ja | ランクマッチに累計5回挑戦しよう |

---

## 設定時のポイント

1. **ミッションの2種類の使い分け**: 通常ミッションは `group_key` を設定して `bonus_point` を付与し、累計ポイントミッションは `criterion_type = MissionBonusPoint` とし `mst_mission_reward_group_id` に報酬グループを設定する
2. **bonus_pointの設計**: 通常ミッション6本すべて達成した場合の合計ポイントが、累計ポイントミッションの最大しきい値以上になるよう設計する
3. **destination_scene**: ミッションタップ時の遷移先。該当する画面名を正確に指定する（`Home`、`Pvp`、`IdleIncentive`、`StageSelect` など）
4. **I18nの必須設定**: すべてのidに対して対応するI18nレコードを作成する。説明文が未設定だと画面上で空欄になる
5. **sort_orderの設計**: 通常ミッション（1〜9）とボーナスポイントミッション（10以降）で番号帯を分けると管理しやすい
6. **criterion_valueはほぼNULL**: 現状定義されている criterion_type では criterion_value を使用するものはない
7. **MissionBonusPointミッションのdestination_scene**: NULL設定が可。累計ポイント達成ミッションはタップ時の遷移先が不要な場合はNULLにする
