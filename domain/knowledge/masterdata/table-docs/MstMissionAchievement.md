# MstMissionAchievement 詳細説明

> CSVパス: `projects/glow-masterdata/MstMissionAchievement.csv`
> i18n CSVパス: `projects/glow-masterdata/MstMissionAchievementI18n.csv`

---

## 1. 概要

`MstMissionAchievement` は**アチーブメントミッションの定義テーブル**。ゲーム全体を通じてプレイヤーが挑戦する実績（アチーブメント）ミッションを設定する。達成条件・開放条件・報酬・UIの遷移先などを一元管理する。

`MstMissionAchievementI18n` は多言語対応のためのサブテーブルで、ミッションの説明文を言語ごとに保持する。

### ゲームプレイへの影響

- アチーブメントは一度達成したらリセットされない永続ミッション
- `criterion_type` / `criterion_count` で達成条件（例: ログイン累計N日）を定義する
- `unlock_criterion_type` / `unlock_criterion_count` で「このミッションが表示されるようになる条件」を設定できる（段階的開放）
- `group_key` を使うと `mission_full_complete`（全ミッションコンプリート）のカウント対象となるグループを管理できる
- `destination_scene` でミッション達成後の遷移先画面を制御する

### テーブル間の関係

```
MstMissionAchievement（アチーブメントミッション本体）
  ├─ id → MstMissionAchievementI18n.mst_mission_achievement_id（1:N、多言語説明文）
  ├─ id → MstMissionAchievementDependency.mst_mission_achievement_id（開放順制御）
  └─ mst_mission_reward_group_id → MstMissionReward.group_id（報酬定義）
```

---

## 2. 全カラム一覧

### mst_mission_achievements（本体テーブル）

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ。`e` = 有効 |
| `id` | varchar(255) | 不可 | - | 主キー（命名規則は後述） |
| `release_key` | int | 不可 | 1 | リリースキー |
| `criterion_type` | varchar(255) | 不可 | - | 達成条件タイプ。`MissionCriterionType` |
| `criterion_value` | varchar(255) | 可 | - | 達成条件の補助値（条件タイプにより意味が変わる。URLや特定IDなど） |
| `criterion_count` | bigint unsigned | 不可 | 0 | 達成に必要な回数・数量 |
| `unlock_criterion_type` | varchar(255) | 可 | - | 開放条件タイプ（NULL or `__NULL__` = 常時表示） |
| `unlock_criterion_value` | varchar(255) | 可 | - | 開放条件の補助値 |
| `unlock_criterion_count` | bigint unsigned | 不可 | 0 | 開放条件の達成回数 |
| `group_key` | varchar(255) | 可 | - | 分類キー。コンプリートカウント対象グループを示す |
| `mst_mission_reward_group_id` | varchar(255) | 不可 | - | 報酬グループID（`mst_mission_rewards.group_id`） |
| `sort_order` | int unsigned | 不可 | 0 | 表示順 |
| `destination_scene` | varchar(255) | 不可 | - | ミッション達成後の遷移先画面名 |

### MstMissionAchievementI18n カラム一覧

| カラム名 | 型 | NULL許容 | デフォルト値 | 説明 |
|---------|----|----|------|------|
| `ENABLE` | varchar | - | - | CSVの有効フラグ |
| `id` | varchar(255) | 不可 | - | 主キー |
| `mst_mission_achievement_id` | varchar(255) | 不可 | - | 対応するアチーブメントID |
| `language` | enum('ja') | 不可 | - | 言語コード |
| `description` | varchar(255) | 不可 | - | ミッション説明文 |
| `release_key` | bigint | 不可 | 1 | リリースキー |

---

## 4. 命名規則 / IDの生成ルール

### 本体テーブル（MstMissionAchievement）

```
achievement_{グループ番号}_{連番}
```

例: `achievement_2_1`、`achievement_2_2`

### i18nテーブル（MstMissionAchievementI18n）

```
{achievement_id}_{言語コード}
```

例: `achievement_2_1_ja`

---

## 5. 他テーブルとの連携

### 参照するテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_rewards` | `mst_mission_reward_group_id → mst_mission_rewards.group_id` | ミッション報酬 |

### 参照されるテーブル

| テーブル | カラム | 説明 |
|---------|--------|------|
| `mst_mission_achievements_i18n` | `mst_mission_achievement_id → mst_mission_achievements.id` | 多言語説明文 |
| `mst_mission_achievement_dependencies` | `mst_mission_achievement_id → mst_mission_achievements.id` | 開放順制御 |

---

## 6. 実データ例

### パターン1: SNSフォロー系アチーブメント

| id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | group_key | mst_mission_reward_group_id | sort_order | destination_scene |
|---|---|---|---|---|---|---|---|---|
| achievement_2_1 | FollowCompleted | https://x.com/jumpplus_jumble | 1 | __NULL__ | NULL | achievement_2_1 | 1 | Web |
| achievement_2_3 | AccessWeb | https://jumble-rush-link.bn-ent.net/ | 1 | __NULL__ | NULL | achievement_2_3 | 3 | Web |

- `FollowCompleted` / `AccessWeb` でSNSやWEBへの誘導ミッション
- `criterion_value` にURLを指定してWeb画面に遷移させる

### パターン2: ログイン累計系アチーブメント

| id | criterion_type | criterion_value | criterion_count | unlock_criterion_type | group_key | destination_scene |
|---|---|---|---|---|---|---|
| achievement_2_4 | LoginCount | NULL | 10 | __NULL__ | NULL | Home |
| achievement_2_5 | LoginCount | NULL | 20 | __NULL__ | NULL | Home |
| achievement_2_6 | LoginCount | NULL | 30 | __NULL__ | NULL | Home |

- 段階的なログイン累計ミッション
- `criterion_value` は NULL（条件値が不要な場合）

### i18n説明文例

| id | mst_mission_achievement_id | language | description |
|---|---|---|---|
| achievement_2_1_ja | achievement_2_1 | ja | 「ジャンブルラッシュ」の公式Xをフォローしよう |
| achievement_2_4_ja | achievement_2_4 | ja | 累計10日ログインしよう |

---

## 7. 設定時のポイント

- アチーブメントは永続ミッションなので `release_key` は安定したバージョンキーを使用する（初回実装以降変えないのが望ましい）
- `unlock_criterion_type` に `__NULL__`（文字列）を設定すると常時表示。NULL（空）とは異なる扱いに注意する
- `group_key` を設定するとコンプリート判定の対象グループになる。グループ完全達成で特典が発生する場合は必ず設定する
- `criterion_value` が不要な条件（`LoginCount` など）は NULL を設定し、URLが必要な条件（`FollowCompleted` など）は URL 文字列を設定する
- `destination_scene` はゲームクライアントの画面名と完全一致させる必要がある
- 本体レコード追加時は必ず対応する i18n レコード（言語 `ja`）も追加する
- 開放順制御が必要な場合は `MstMissionAchievementDependency` にも設定を追加する
- クライアントクラス: `MstMissionAchievementData.cs` / `MstMissionAchievementI18nData.cs`（`MissionCriterionType` enum を使用）
