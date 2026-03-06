# MstMissionLimitedTerm 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionLimitedTerm.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionLimitedTermI18n.csv`

---

## 1. 概要

`MstMissionLimitedTerm` は**期間限定ミッションの定義テーブル**。特定の期間（`start_at` / `end_at`）のみ有効なミッションを設定する。降臨バトル・アートワークパネル・ダンジョンなどのコンテンツに紐づいた期間限定ミッションを管理する。

`MstMissionLimitedTermI18n` はミッション説明文の多言語データを保持するサブテーブル。

### ゲームプレイへの影響

- `start_at` / `end_at` で有効期間を制御するため、特定イベントやコンテンツに合わせた限定ミッションを設定できる
- `mission_category` で降臨バトル・アートワークパネル・ダンジョンなどのコンテンツ種別を識別する
- `reset_type` でリセット条件（復刻ごと or 月次）を制御する
- `progress_group_key` で同じ進捗グループに属するミッション群を管理する
- `MstMissionLimitedTermDependency` と組み合わせて段階的開放を実現できる

### テーブル間の関係

```
MstMissionLimitedTerm（期間限定ミッション本体）
  ├─ id → MstMissionLimitedTermI18n.mst_mission_limited_term_id（1:N、多言語）
  ├─ id → MstMissionLimitedTermDependency.mst_mission_limited_term_id（開放順制御）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）

progress_group_key = "group1" でグループ化した降臨バトルミッション群
  ├─ limited_term_1（5回挑戦）
  ├─ limited_term_2（10回挑戦）
  ├─ limited_term_3（20回挑戦）
  └─ limited_term_4（30回挑戦）
```

---

## 2. 全カラム一覧

### mst_mission_limited_terms（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `progress_group_key` | varchar(255) | 不可 | - | 進捗グループキー（同グループのミッションをまとめる） |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値 |
| `criterion_count` | bigint unsigned | 不可 | - | 達成に必要な回数・数量 |
| `mission_category` | enum('AdventBattle','ArtworkPanel','Dungeon') | 不可 | - | ミッションカテゴリー |
| `reset_type` | enum('Revival','Monthly') | 不可 | `Revival` | リセット条件タイプ |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID |
| `sort_order` | int unsigned | 不可 | - | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |
| `start_at` | timestamp | 不可 | - | ミッション開始日時（UTC） |
| `end_at` | timestamp | 不可 | - | ミッション終了日時（UTC） |

### MstMissionLimitedTermI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_limited_term_id` | varchar(255) | 不可 | - | 対応する期間限定ミッションID |
| `language` | enum('ja') | 不可 | `ja` | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |

### ユニーク制約（i18nテーブル）

| インデックス名 | カラム | 説明 |
|---|---|---|
| `mst_mission_limited_term_id_language_unique` | (mst_mission_limited_term_id, language) | 同ミッション×同言語の重複不可 |

---

## 3. 主要なenumの解説

### MissionCategory（ミッションカテゴリー）

| 値 | 対応コンテンツ |
|---|---|
| `AdventBattle` | 降臨バトル |
| `ArtworkPanel` | アートワークパネル |
| `Dungeon` | ダンジョン |

### ResetType（リセット条件）

| 値 | リセットタイミング |
|---|---|
| `Revival` | 復刻時にリセット（デフォルト） |
| `Monthly` | 毎月リセット |

---

## 4. 命名規則 / IDの生成ルール

### 本体テーブル（MstMissionLimitedTerm）

```
limited_term_{連番}
```

例: `limited_term_1`、`limited_term_2`

### i18nテーブル（MstMissionLimitedTermI18n）

```
{limited_term_id}_{言語コード}
```

例: `limited_term_1_ja`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | 報酬定義 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_limited_terms_i18n` | `mst_mission_limited_term_id → mst_mission_limited_terms.id` | 多言語説明文 |
| `mst_mission_limited_term_dependencies` | `mst_mission_limited_term_id → mst_mission_limited_terms.id` | 開放順制御 |

---

## 6. 実データ例

### パターン1: 降臨バトル系ミッション（古橋 伊春イベント）

| id | release_key | progress_group_key | criterion_type | criterion_count | mission_category | reset_type | mst_mission_reward_group_id | start_at | end_at |
|---|---|---|---|---|---|---|---|---|---|
| limited_term_1 | 202509010 | group1 | AdventBattleChallengeCount | 5 | AdventBattle | Revival | kai_00001_limited_term_1 | 2025-10-01 12:00:00 | 2025-10-08 11:59:59 |
| limited_term_2 | 202509010 | group1 | AdventBattleChallengeCount | 10 | AdventBattle | Revival | kai_00001_limited_term_2 | 2025-10-01 12:00:00 | 2025-10-08 11:59:59 |
| limited_term_3 | 202509010 | group1 | AdventBattleChallengeCount | 20 | AdventBattle | Revival | kai_00001_limited_term_3 | 2025-10-01 12:00:00 | 2025-10-08 11:59:59 |
| limited_term_4 | 202509010 | group1 | AdventBattleChallengeCount | 30 | AdventBattle | Revival | kai_00001_limited_term_4 | 2025-10-01 12:00:00 | 2025-10-08 11:59:59 |

- 5回→10回→20回→30回と段階的な挑戦回数ミッション
- 同一グループ（`group1`）で同じ期間を設定

### パターン2: i18n説明文例

| id | mst_mission_limited_term_id | language | description |
|---|---|---|---|
| limited_term_1_ja | limited_term_1 | ja | 降臨バトル「怪獣退治の時間」に5回挑戦しよう！ |
| limited_term_2_ja | limited_term_2 | ja | 降臨バトル「怪獣退治の時間」に10回挑戦しよう！ |
| limited_term_5_ja | limited_term_5 | ja | 降臨バトル「SPY×FAMILY」に5回挑戦しよう！ |

---

## 7. 設定時のポイント

- `start_at` / `end_at` は UTC タイムスタンプで設定する（JST から9時間引く）
- `progress_group_key` は同一コンテンツ・同一期間に属するミッション群で統一する（例: `group1`、`group2`...）
- `mission_category` は対象コンテンツの種類に合わせて正確に設定する
- `reset_type` は通常 `Revival`（復刻時にリセット）だが、月次リセットが必要な場合は `Monthly` を設定する
- 本体レコード追加時は対応する i18n レコード（言語 `ja`）を必ず追加する
- 同一期間・同一カテゴリーのミッション群を `progress_group_key` でグループ化し、必要であれば `MstMissionLimitedTermDependency` で開放順も設定する
- クライアントクラス: `MstMissionLimitedTermData.cs` / `MstMissionLimitedTermI18nData.cs`（`MissionCriterionType`、`MissionCategory` enum を使用）
