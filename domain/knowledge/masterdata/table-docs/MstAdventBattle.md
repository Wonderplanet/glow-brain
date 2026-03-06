# MstAdventBattle 詳細説明

> CSVパス: `projects/glow-masterdata/MstAdventBattle.csv`

> i18n CSVパス: `projects/glow-masterdata/MstAdventBattleI18n.csv`

---

## 概要

降臨バトルの基本設定を管理するテーブル。降臨バトルとは、期間限定で開催されるイベントバトルコンテンツで、スコアチャレンジやレイドなどのタイプが存在する。

バトルの開催期間、挑戦可能回数、報酬の基本情報（EXP・コイン）、インゲームの初期バトルポイント、スコア加算設定などを管理する。イベントの枠組み（`mst_events`）、インゲーム設定（`mst_in_games`）、報酬グループ（`mst_advent_battle_reward_groups`）などの複数テーブルと連携して1つのイベントを構成する。

---

## 全カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。降臨バトルを一意に識別するID |
| mst_event_id | varchar(255) | YES | 紐付くイベントの `mst_events.id`。空文字の場合もある |
| mst_in_game_id | varchar(255) | YES | 使用するインゲーム設定の `mst_in_games.id` |
| asset_key | varchar(255) | YES | バナー・背景等のアセットキー |
| advent_battle_type | enum | YES | 降臨バトルのタイプ（`ScoreChallenge` / `Raid`） |
| initial_battle_point | int | YES | インゲーム開始時のリーダーポイント初期値 |
| score_addition_type | varchar(255) | YES | スコア加算タイプ（例: `AllEnemiesAndOutPost`） |
| score_additional_coef | decimal(5,3) | YES | スコア加算係数 |
| score_addition_target_mst_enemy_stage_parameter_id | varchar(255) | NO | スコア加算対象の敵ステージパラメータID |
| mst_stage_rule_group_id | varchar(255) | NO | ステージルールグループのID（NULL可） |
| event_bonus_group_id | varchar(255) | YES | イベントボーナスユニットのグループID（`mst_event_bonus_units.event_bonus_group_id`） |
| challengeable_count | smallint unsigned | YES | 1日の挑戦可能回数 |
| ad_challengeable_count | smallint unsigned | YES | 1日の広告視聴で追加される挑戦可能回数 |
| display_mst_unit_id1 | varchar(255) | NO | 降臨バトルトップ表示位置1のキャラID（NULL可） |
| display_mst_unit_id2 | varchar(255) | NO | 降臨バトルトップ表示位置2のキャラID（NULL可） |
| display_mst_unit_id3 | varchar(255) | NO | 降臨バトルトップ表示位置3のキャラID（NULL可） |
| exp | int unsigned | YES | バトルクリア時の獲得リーダーEXP |
| coin | int unsigned | YES | バトルクリア時の獲得コイン |
| start_at | timestamp | YES | 降臨バトル開始日時 |
| end_at | timestamp | YES | 降臨バトル終了日時 |
| release_key | bigint | YES | リリースキー |

### MstAdventBattleI18n カラム一覧

| カラム名 | 型 | 必須 | 説明 |
|---------|----|----|------|
| ENABLE | varchar | YES | 有効フラグ（`e` = 有効） |
| id | varchar(255) | YES | UUID。命名規則: `{mst_advent_battle_id}_{language}` |
| mst_advent_battle_id | varchar(255) | YES | 参照先の `mst_advent_battles.id` |
| language | enum('ja') | YES | 言語設定。現在は `ja`（日本語）のみ |
| name | varchar(255) | YES | 降臨バトルの表示名称 |
| boss_description | varchar(255) | YES | ボスの説明文（空文字の場合あり） |
| release_key | bigint | YES | リリースキー |

---

## advent_battle_type（降臨バトルタイプ）

| 値 | 説明 |
|----|------|
| `ScoreChallenge` | スコアチャレンジ形式。プレイヤーのスコアを競う |
| `Raid` | レイド形式。複数プレイヤーで協力してボスを倒す |

---

## 命名規則 / IDの生成ルール

- `id`: `quest_raid_{シリーズ略称}_{連番}`（例: `quest_raid_kai_00001`、`quest_raid_spy1_00001`）
- i18nの `id`: `{mst_advent_battle_id}_{language}`（例: `quest_raid_kai_00001_ja`）

---

## 他テーブルとの連携

| テーブル | 関係 | 説明 |
|---------|------|------|
| `mst_events` | 多対1 | イベントの枠組み定義。`mst_event_id` で参照 |
| `mst_in_games` | 多対1 | インゲーム（ステージ）設定。`mst_in_game_id` で参照 |
| `mst_advent_battles_i18n` | 1対多 | 表示名・ボス説明文の多言語管理 |
| `mst_advent_battle_clear_rewards` | 1対多 | クリア報酬の設定 |
| `mst_advent_battle_ranks` | 1対多 | ランク（Bronze/Silver/Gold/Master）定義 |
| `mst_advent_battle_reward_groups` | 1対多 | スコア達成・ランキング報酬グループ |
| `mst_event_bonus_units` | 多対多 | イベントボーナス対象ユニット。`event_bonus_group_id` で参照 |
| `mst_stage_event_rules` | 多対1 | ステージイベントルール。`mst_stage_rule_group_id` で参照 |

---

## 実データ例

### パターン1: 通常のスコアチャレンジ降臨バトル

```
ENABLE: e
id: quest_raid_kai_00001
mst_event_id: event_kai_00001
mst_in_game_id: raid_kai_00001
asset_key: kai_00002
advent_battle_type: ScoreChallenge
initial_battle_point: 500
score_addition_type: AllEnemiesAndOutPost
score_additional_coef: 0.07
event_bonus_group_id: raid_kai_00001
challengeable_count: 3
ad_challengeable_count: 2
display_mst_unit_id1: enemy_kai_00001
display_mst_unit_id2: enemy_kai_00101
display_mst_unit_id3: enemy_kai_00101
exp: 100
coin: 300
start_at: 2025-10-01 12:00:00
end_at: 2025-10-08 11:59:59
release_key: 202509010
```

```
ENABLE: e
id: quest_raid_kai_00001_ja
mst_advent_battle_id: quest_raid_kai_00001
language: ja
name: 怪獣退治の時間
boss_description: ボスを倒して高スコア獲得!!
release_key: 202509010
```

### パターン2: イベントなしの再開催

```
ENABLE: e
id: quest_raid_kai_00002
mst_event_id: （空文字）
mst_in_game_id: raid_kai_00001
asset_key: kai_00002
advent_battle_type: ScoreChallenge
initial_battle_point: 500
score_addition_type: AllEnemiesAndOutPost
score_additional_coef: 0.07
event_bonus_group_id: raid_kai_00001
challengeable_count: 3
ad_challengeable_count: 2
exp: 100
coin: 300
start_at: 2025-11-12 15:00:00
end_at: 2025-11-17 14:59:59
release_key: 202511010
```

---

## 設定時のポイント

1. **開催期間の時刻設定**: `start_at` と `end_at` はタイムスタンプで設定する。終了時刻は通常 `14:59:59` または `11:59:59` となる（日本時間の深夜0時に相当するケースが多い）。
2. **challengeable_count と ad_challengeable_count の組み合わせ**: 無料挑戦回数（`challengeable_count`）と広告視聴での追加挑戦（`ad_challengeable_count`）を別途設定する。現在は3回 + 広告2回が標準的な設定。
3. **mst_event_id の省略**: 単独コンテンツとして開催される場合やイベントと独立している場合、`mst_event_id` を空文字にできる。
4. **display_mst_unit_id の設定**: 降臨バトルのトップ画面に表示するキャラを最大3体設定できる。設定しない場合はNULLを指定する。
5. **score_additional_coef の調整**: スコア加算係数はバランス調整の重要な数値。現状は `0.07` が標準値だが、コンテンツの難易度や報酬設計に合わせて調整する。
6. **event_bonus_group_id の命名**: `mst_in_game_id` と同じ値を設定するパターンが多いが、必ずしも一致しなくてよい。
7. **i18nとのセット作成**: 降臨バトルを新規追加する際は、メインテーブルと同時に i18n テーブルのレコードも必ず作成する。
