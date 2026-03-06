# 降臨バトル マスタデータ設定手順書

## 概要

降臨バトル（スコアチャレンジ型・ランキング型）の設定手順書。バトル本体・報酬・ランク定義・エンブレムをカバーする。

- **report.md 対応セクション**: `### 3. 降臨バトル機能`

---

## 対象テーブル一覧と設定順序

| 作業順 | テーブル名 | 役割 | 必須/任意 |
|-------|-----------|------|---------|
| 1 | MstEmblem | エンブレム定義 | 必須 |
| 2 | MstEmblemI18n | エンブレム多言語名 | 必須 |
| 3 | MstAdventBattle | 降臨バトル本体 | 必須 |
| 4 | MstAdventBattleI18n | 降臨バトル多言語名 | 必須 |
| 5 | MstAdventBattleRewardGroup | 報酬グループ定義 | 必須 |
| 6 | MstAdventBattleReward | 報酬詳細 | 必須 |
| 7 | MstAdventBattleClearReward | クリア報酬（ランダム） | 必須 |
| 8 | MstAdventBattleRank | ランク定義 | 必須 |

---

## 前提条件・依存関係

- **MstEvent の登録完了が前提**（`01_event.md` を先に実施）
- **MstUnit の登録完了が前提**（`02_unit.md` を先に実施）
- **インゲームデータ（MstInGame 等）の登録完了が前提**（`09_ingame-battle.md` または `masterdata-ingame-creator` スキルを先に実施）
- MstAdventBattle.event_bonus_group_id は MstEventBonusUnit と同一グループ名（`01_event.md` 参照）
- MstStage/MstStageEndCondition は `04_quest-stage.md` で設定（降臨バトルのステージ終了条件含む）

---

## report.md から読み取る情報チェックリスト

- [ ] 降臨バトル ID（例: `quest_raid_you1_00001`）
- [ ] バトルタイプ（ScoreChallenge/Ranking/...）
- [ ] バトル名・ボス説明
- [ ] 開催期間（start_at / end_at）
- [ ] 挑戦回数（challengeable_count）
- [ ] 広告挑戦回数（ad_challengeable_count）
- [ ] 報酬一覧（スコア到達報酬・ランキング報酬）
- [ ] ランク種別・スコア閾値
- [ ] エンブレム一覧（ランキング報酬として配布）

---

## テーブル別設定手順

### MstEmblem（エンブレム定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `emblem_{type}_{series_id}_{連番}` | `emblem_adventbattle_you_season01_00001` |
| emblemType | エンブレム種別（Event/...） | `Event` |
| mstSeriesId | シリーズ ID | `you` |
| assetKey | アセットキー | `adventbattle_you_season01_00001` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ（masterdata-explorer）**

```duckdb
SELECT id, emblemType, mstSeriesId, assetKey, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstEmblem.csv')
ORDER BY release_key DESC, emblemType, id;
```

---

### MstEmblemI18n（エンブレム多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_emblem_id}_{language}` | `emblem_adventbattle_you_season01_00001_ja` |
| mst_emblem_id | 対応する MstEmblem.id | `emblem_adventbattle_you_season01_00001` |
| language | 言語コード | `ja` |
| name | エンブレム表示名 | `元・伝説の殺し屋(1位)` |
| description | エンブレム説明文 | `2026年2月開催降臨バトル『誰の依頼だ？』1位の証` |

---

### MstAdventBattle（降臨バトル本体）

**バトルタイプ一覧**

| advent_battle_type | 説明 |
|-------------------|------|
| ScoreChallenge | スコアを競うチャレンジ型 |
| Ranking | ランキング型 |

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `quest_raid_{series}{回}_{連番}` | `quest_raid_you1_00001` |
| mst_event_id | 対応するイベント ID | `event_you_00001` |
| mst_in_game_id | インゲームデータ ID | `raid_you1_00001` |
| asset_key | アセットキー | `you_00003` |
| advent_battle_type | バトルタイプ（上表参照） | `ScoreChallenge` |
| initial_battle_point | 初期バトルポイント | `500` |
| score_addition_type | スコア加算対象（AllEnemiesAndOutPost/...） | `AllEnemiesAndOutPost` |
| score_additional_coef | スコア加算係数 | `0.07` |
| score_addition_target_mst_enemy_stage_parameter_id | 特定敵スコア対象 ID | `test` |
| mst_stage_rule_group_id | ステージルールグループ（なければ NULL） | `NULL` |
| event_bonus_group_id | イベントボーナスグループ（MstEventBonusUnit と同一） | `raid_you1_00001` |
| challengeable_count | 挑戦可能回数 | `3` |
| ad_challengeable_count | 広告追加挑戦回数 | `2` |
| display_mst_unit_id1〜3 | 表示ユニット ID（最大3体） | `chara_you_00301` |
| exp | 獲得 EXP | `100` |
| coin | 獲得コイン | `300` |
| start_at | 開始日時（UTC） | `2026-02-09 15:00:00` |
| end_at | 終了日時（UTC） | `2026-02-15 14:59:59` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_event_id, mst_in_game_id, advent_battle_type,
       challengeable_count, ad_challengeable_count, start_at, end_at, release_key
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstAdventBattle.csv');
```

---

### MstAdventBattleI18n（降臨バトル多言語名）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| release_key | 今回のリリースキー | `202602015` |
| id | `{mst_advent_battle_id}_{language}` | `quest_raid_you1_00001_ja` |
| mst_advent_battle_id | 対応する MstAdventBattle.id | `quest_raid_you1_00001` |
| language | 言語コード | `ja` |
| name | 降臨バトル表示名 | `誰の依頼だ？` |
| boss_description | ボス説明文 | `ボスを倒して高スコア獲得!!` |

---

### MstAdventBattleRewardGroup（報酬グループ定義）

スコア到達報酬のグループを定義する。

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{advent_battle_id}_reward_group_{連番5桁}_{連番2桁}` | `quest_raid_you1_reward_group_00001_01` |
| mst_advent_battle_id | 対応する MstAdventBattle.id | `quest_raid_you1_00001` |
| reward_category | 報酬カテゴリ（MaxScore/...） | `MaxScore` |
| condition_value | 条件値（スコア閾値） | `5000` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_advent_battle_id, reward_category, condition_value
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstAdventBattleRewardGroup.csv')
ORDER BY mst_advent_battle_id, CAST(condition_value AS INTEGER);
```

---

### MstAdventBattleReward（報酬詳細）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{advent_battle_reward_group_id}_{連番}` | `quest_raid_you1_reward_group_00001_01_1` |
| mst_advent_battle_reward_group_id | 対応する RewardGroup.id | `quest_raid_you1_reward_group_00001_01` |
| resource_type | リソース種別（FreeDiamond/Coin/Item/Unit） | `FreeDiamond` |
| resource_id | リソース ID（Coin/Diamond は NULL） | `NULL` |
| resource_amount | 報酬量 | `20` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstAdventBattleClearReward（クリア報酬・ランダム）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{advent_battle_id}_{連番}` | `quest_raid_you1_00001_1` |
| mst_advent_battle_id | 対応する MstAdventBattle.id | `quest_raid_you1_00001` |
| reward_category | 報酬カテゴリ（Random/FirstClear/...） | `Random` |
| resource_type | リソース種別 | `Item` |
| resource_id | リソース ID | `memory_glo_00001` |
| resource_amount | 報酬量 | `3` |
| percentage | ドロップ率（%） | `20` |
| sort_order | 表示順 | `1` |
| release_key | 今回のリリースキー | `202602015` |

---

### MstAdventBattleRank（ランク定義）

**カラム設定ガイド**

| カラム名 | 設定値の決め方 | 具体例 |
|---------|--------------|-------|
| ENABLE | 常に `e` | `e` |
| id | `{advent_battle_id}_rank_{連番2桁}` | `quest_raid_you1_00001_rank_01` |
| mst_advent_battle_id | 対応する MstAdventBattle.id | `quest_raid_you1_00001` |
| rank_type | ランク種別（Bronze/Silver/Gold/...） | `Bronze` |
| rank_level | ランクレベル（1 から連番） | `1` |
| required_lower_score | 下限スコア | `1000` |
| asset_key | アセットキー（通常 NULL） | `NULL` |
| release_key | 今回のリリースキー | `202602015` |

**過去データ参照クエリ**

```duckdb
SELECT id, mst_advent_battle_id, rank_type, rank_level, required_lower_score
FROM read_csv('domain/raw-data/masterdata/released/202602015/tables/MstAdventBattleRank.csv')
ORDER BY mst_advent_battle_id, rank_level;
```

---

## 検証方法

- MstAdventBattle.mst_in_game_id → MstInGame.id が存在するか
- MstAdventBattle.event_bonus_group_id → MstEventBonusUnit.event_bonus_group_id が存在するか
- MstAdventBattleReward.mst_advent_battle_reward_group_id → MstAdventBattleRewardGroup.id が存在するか
- MstAdventBattleRank の rank_level が 1 から連番になっているか
- エンブレムが報酬（MstMissionReward 等）に設定されているか

---

## 参照リソース

- DBスキーマ: `projects/glow-server/api/database/schema/exports/master_tables_schema.json`
- 利用スキル: `masterdata-explorer`, `masterdata-csv-validator`, `masterdata-ingame-creator`
- 過去リリース: `domain/raw-data/masterdata/released/202602015/tables/`
- インゲーム設定: `09_ingame-battle.md`
- ステージ設定: `04_quest-stage.md`（MstStageEndCondition）
