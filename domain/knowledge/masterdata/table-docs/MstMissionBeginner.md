# MstMissionBeginner 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionBeginner.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionBeginnerI18n.csv`

---

## 1. 概要

`MstMissionBeginner` は**初心者ミッションの定義テーブル**。新規プレイヤーがゲームを始めた後、一定日数にわたって提示されるチュートリアル的なミッション群を設定する。`unlock_day` によって「登録から何日後にこのミッションが表示されるか」を制御する。

`MstMissionBeginnerI18n` はミッションタイトル・説明文の多言語データを保持するサブテーブル。

### ゲームプレイへの影響

- `unlock_day` でミッションの開放日を管理し、日ごとに段階的に課題を提示する（例: 1日目に4ミッション、2日目に4ミッション...）
- `group_key` でその日のミッション群をまとめ、グループ単位での進捗管理を行う
- `bonus_point` でミッションごとのポイントを設定し、累積ポイントによるボーナス報酬を実現する
- `destination_scene` でミッション達成後の遷移先画面を制御する
- デイリーミッションと異なり、毎日リセットされず日数経過で固定的に提示される

### テーブル間の関係

```
MstMissionBeginner（初心者ミッション本体）
  ├─ id → MstMissionBeginnerI18n.mst_mission_beginner_id（1:N、多言語）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）

unlock_day=1 → group_key=Beginner1 の4ミッション（1日目課題）
unlock_day=2 → group_key=Beginner2 の4ミッション（2日目課題）
...
```

---

## 2. 全カラム一覧

### mst_mission_beginners（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値（クエストIDなど） |
| `criterion_count` | bigint unsigned | 不可 | 0 | 達成に必要な回数・数量 |
| `unlock_day` | smallint unsigned | 不可 | 0 | ゲーム開始からの開放日数（1=初日、2=2日目...） |
| `group_key` | varchar(255) | 可 | - | 分類キー（同日のミッション群をまとめる） |
| `bonus_point` | bigint unsigned | 不可 | 0 | このミッション達成で得られるボーナスポイント |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_mission_reward_groups.group_id`） |
| `sort_order` | int unsigned | 不可 | - | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |

### MstMissionBeginnerI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `release_key` | bigint | 不可 | 1 | リリースキー |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_beginner_id` | varchar(255) | 不可 | - | 対応する初心者ミッションID |
| `language` | enum('ja') | 不可 | - | 言語コード |
| `title` | varchar(255) | 不可 | - | ダイアログタイトル |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |

---

## 4. 命名規則 / IDの生成ルール

### 本体テーブル（MstMissionBeginner）

```
beginner{バージョン}_{日数}_{連番}
```

例: `beginner2_1_1`（バージョン2、1日目、1番目のミッション）

### i18nテーブル（MstMissionBeginnerI18n）

```
{beginner_id}_{言語コード}
```

例: `beginner2_1_1_ja`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | ミッション報酬 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_beginners_i18n` | `mst_mission_beginner_id → mst_mission_beginners.id` | 多言語タイトル・説明 |

---

## 6. 実データ例

### パターン1: 1日目のミッション群

| id | criterion_type | criterion_value | criterion_count | unlock_day | group_key | bonus_point | destination_scene |
|---|---|---|---|---|---|---|---|
| beginner2_1_1 | LoginCount | NULL | 1 | 1 | Beginner1 | 20 | Home |
| beginner2_1_2 | IdleIncentiveCount | NULL | 1 | 1 | Beginner1 | 30 | IdleIncentive |
| beginner2_1_3 | UnitLevelUpCount | NULL | 5 | 1 | Beginner1 | 40 | UnitList |
| beginner2_1_4 | SpecificQuestClear | quest_main_spy_normal_1 | 1 | 1 | Beginner1 | 50 | QuestSelect |

- 1日目に4つのミッションが解放される
- ポイントは20、30、40、50と段階的に設定

### パターン2: 2日目のミッション群

| id | criterion_type | criterion_value | criterion_count | unlock_day | group_key | bonus_point | destination_scene |
|---|---|---|---|---|---|---|---|
| beginner2_2_1 | LoginCount | NULL | 2 | 2 | Beginner2 | 20 | Home |
| beginner2_2_2 | OutpostEnhanceCount | NULL | 1 | 2 | Beginner2 | 30 | OutpostEnhance |
| beginner2_2_3 | UnitLevelUpCount | NULL | 10 | 2 | Beginner2 | 40 | UnitList |
| beginner2_2_4 | ArtworkCompletedCount | NULL | 1 | 2 | Beginner2 | 50 | QuestSelect |

### i18n説明文例

| id | mst_mission_beginner_id | language | title | description |
|---|---|---|---|---|
| beginner2_1_1_ja | beginner2_1_1 | ja | NULL | 1日ログインしよう |
| beginner2_1_4_ja | beginner2_1_4 | ja | NULL | メインクエスト「SPY×FAMILY」の難易度ノーマルをクリアしよう |

---

## 7. 設定時のポイント

- `unlock_day` で日数管理を行うため、追加ミッションは必ず正しい日数を設定する
- 同日のミッションは `group_key` を統一する（例: `Beginner1` / `Beginner2`）
- `bonus_point` はデイリーミッションのボーナスポイントシステムと連動しているため、合計ポイントが報酬の閾値と整合するよう設定する
- `SpecificQuestClear` など特定コンテンツに誘導するミッションは `criterion_value` にIDを設定する
- `sort_order` はCSVでは NULL になっている場合があるが、本番DBには整数値を入れる必要がある
- 本体レコード追加時は必ず対応する i18n レコード（言語 `ja`）も追加する。`title` は現行データで NULL が多いが設定可能
- クライアントクラス: `MstMissionBeginnerData.cs` / `MstMissionBeginnerI18nData.cs`（`MissionCriterionType` enum を使用）
