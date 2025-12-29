# マスターテーブル実装的リレーション一覧

このドキュメントは、GLOWプロジェクトのマスターテーブル間の**実装的リレーション**をまとめたものです。

## 実装的リレーションとは

DDLで明示的に定義されていないが、実際のコード内で「あるマスターデータから別のマスターデータを取得している」実装パターンのことです。

## 調査方法

- glow-server: PHP（Laravel）コードのEntity、Repository、Service、UseCaseを調査
- glow-client: C#（Unity）コードのModel、Repository、Service、UseCaseを調査
- 調査範囲: adminとtestディレクトリは除外

## 調査済み領域

本ドキュメントは以下の領域について調査済みです：
- ✅ クエスト(Quest)関連
- ✅ ステージ(Stage)関連
- ✅ インゲーム(InGame)関連

---

# クエスト(Quest)関連の実装的リレーション

## glow-server

### mst_questsからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_quests.mst_event_id | mst_events.id | イベント情報取得 | MstQuestEntity.php:14,41 |
| mst_quests.mst_series_id | mst_series.id | シリーズ情報取得 | MstQuestEntity.php:15,46 |

### mst_stagesからmst_questsへの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.mst_quest_id | mst_quests.id | ステージのクエスト情報 | MstStageEntity.php:13,33 |
| mst_stages.mst_quest_id | mst_quests.id | クエスト期間チェック | StageStartQuestService.php |
| mst_stages.mst_quest_id | mst_quests.id | 初回クリア判定 | StageEndQuestService.php:279 |

### mst_quest_bonus_units

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_quest_bonus_units.mst_quest_id | mst_quests.id | ボーナス対象クエスト | MstQuestBonusUnitEntity.php:11,24 |
| mst_quest_bonus_units.mst_unit_id | mst_units.id | ボーナスユニット | MstQuestBonusUnitEntity.php:12,29 |

### mst_quest_event_bonus_schedules

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_quest_event_bonus_schedules.mst_quest_id | mst_quests.id | ボーナス期間対象 | MstQuestEventBonusScheduleEntity.php:12,29 |
| mst_quest_event_bonus_schedules.event_bonus_group_id | mst_event_bonus_units.event_bonus_group_id | イベントボーナスグループ | MstQuestEventBonusScheduleEntity.php:13,34 |

### opr_campaigns

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| opr_campaigns.target_id | mst_quests.id | キャンペーン対象（target_id_type='Quest'） | OprCampaignRepository.php:116 |
| opr_campaigns.target_id | mst_series.id | キャンペーン対象（target_id_type='Series'） | OprCampaignRepository.php:121 |

### その他

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stage_event_settings.mst_stage_id | mst_stages.id | イベントステージ設定 | MstStageEventSettingEntity.php:11,27 |
| mst_stage_rewards.mst_stage_id | mst_stages.id | ステージ報酬 | MstStageRewardEntity.php:14,29 |
| mst_stage_event_rewards.mst_stage_id | mst_stages.id | イベントステージ報酬 | MstStageEventRewardEntity.php:14,29 |

## glow-client

### mst_questsからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_quests.mst_event_id | mst_events.id | イベント情報取得 | MstQuestModel.cs:15 |
| mst_quests.group_id | - | 難易度違いのグループ化 | QuestSelectUseCase.cs:40-41 |

### mst_stagesからmst_questsへの逆参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.mst_quest_id | mst_quests.id | クエストからステージ取得 | IMstStageDataRepository.cs:12 |
| mst_stages.mst_quest_id | mst_quests.id | ステージ情報表示 | EventQuestTopUseCaseElementModelFactory.cs:50 |

### その他

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_quest_bonus_units.mst_quest_id | mst_quests.id | ボーナスユニット | MstQuestBonusUnitModel.cs:9 |
| mst_quest_event_bonus_schedules.mst_quest_id | mst_quests.id | イベントボーナス期間 | MstQuestEventBonusScheduleModel.cs:8 |

---

# ステージ(Stage)関連の実装的リレーション

## glow-server

### mst_stagesからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.mst_quest_id | mst_quests.id | クエスト情報 | MstStageEntity.php:13,33 |
| mst_stages.mst_in_game_id | mst_in_games.id | インゲーム設定 | MstStageEntity.php:9,21 |
| mst_stages.prev_mst_stage_id | mst_stages.id | 前ステージ（開放条件） | StageStartQuestService.php:153 |
| mst_stages.mst_artwork_fragment_drop_group_id | mst_artwork_fragment_drop_groups.id | 原画かけらドロップ | MstStageEntity.php:53 |

### mst_stagesへの参照（逆参照）

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stage_rewards.mst_stage_id | mst_stages.id | ステージ報酬 | StageService.php:187-199 |
| mst_stage_event_rewards.mst_stage_id | mst_stages.id | イベント報酬 | StageService.php:187-221 |
| mst_stage_clear_time_rewards.mst_stage_id | mst_stages.id | クリアタイム報酬 | StageEndQuestService.php:505-533 |
| mst_stage_event_settings.mst_stage_id | mst_stages.id | イベント設定 | StageStartEventQuestService.php:88-93 |
| mst_in_game_special_rules.target_id | mst_stages.id | 特殊ルール（content_type='stage'） | StageService.php:306-310 |

### キャンペーンとの関連

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| opr_campaigns → | mst_quests → mst_stages | スタミナ・報酬倍率 | StageService.php:134-162 |

## glow-client

### mst_stagesからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.mst_quest_id | mst_quests.id | クエスト情報 | MstStageModel.cs |
| mst_stages.mst_in_game_id | mst_in_games.id | インゲーム設定 | MstStageModel.cs |
| mst_stages.mst_page_id | mst_pages.id | コマページ | MstStageModel.cs |
| mst_stages.mst_enemy_outpost_id | mst_enemy_outposts.id | 敵拠点 | MstStageModel.cs |
| mst_stages.mst_defense_target_id | mst_defense_targets.id | 防衛対象 | MstStageModel.cs |
| mst_stages.boss_mst_enemy_stage_parameter_id | mst_enemy_stage_parameters.id | ボスパラメータ | MstStageModel.cs |
| mst_stages.mst_artwork_fragment_drop_group_id | mst_artwork_fragment_drop_groups.id | 原画かけらドロップ | MstStageModel.cs |
| mst_stages.mst_auto_player_sequence_set_id | mst_auto_player_sequence_sets.id | 敵AI | MstStageModel.cs |
| mst_stages.release_required_mst_stage_id | mst_stages.id | 開放条件 | MstStageModel.cs |

### ステージ関連の動的参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.id | mst_stage_rewards | 通常報酬 | HomeStageInfoUseCases.cs:111 |
| mst_stages.id | mst_stage_event_rewards | イベント報酬 | HomeStageInfoUseCases.cs:98 |
| mst_stages.id | mst_stage_clear_time_rewards | タイムアタック報酬 | HomeStageInfoUseCases.cs:165 |
| mst_stages.id | mst_in_game_special_rules | 特殊ルール | HomeStageSelectUseCases.cs:407 |
| mst_stages.id | mst_stage_event_settings | イベント設定 | HomeStageSelectUseCases.cs:245 |
| mst_stages.id | mst_manga_animations | マンガ演出 | InitializeInGameUseCase.cs:268 |

---

# インゲーム(InGame)関連の実装的リレーション

## glow-server

### mst_in_gamesからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_in_games.mst_auto_player_sequence_set_id | mst_auto_player_sequences.id | 敵AI設定 | MstInGame.php:18-19 |
| mst_in_games.mst_page_id | mst_pages.id | コマページ | MstInGame.php:24 |
| mst_in_games.mst_enemy_outpost_id | mst_enemy_outposts.id | 敵拠点 | MstInGame.php:25 |
| mst_in_games.mst_defense_target_id | mst_defense_targets.id | 防衛対象 | MstInGame.php:26 |
| mst_in_games.boss_mst_enemy_stage_parameter_id | mst_enemy_stage_parameters.id | ボスパラメータ | MstInGame.php:27 |

### mst_in_gamesへの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_pvps.mst_in_game_id | mst_in_games.id | PvPのインゲーム | MstPvpEntity.php:44-46 |
| mst_advent_battles.mst_in_game_id | mst_in_games.id | 降臨バトル | (推測) |

### mst_in_game_special_rules

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_in_game_special_rules.content_type + target_id | mst_stages.id / mst_advent_battles.id | 対象コンテンツ | MstInGameSpecialRuleRepository.php:25-41 |
| mst_in_game_special_rules.rule_type | - | ルールタイプ | MstInGameSpecialRule.php:21 |

### 敵発見処理

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| discovered_enemies → | mst_enemy_characters.id | 敵キャラ情報 | InGameEnemyService.php:43-48 |
| mst_enemy_characters.mst_series_id | mst_series.id | シリーズ情報 | DiscoveredEnemy.php:29 |

## glow-client

### mst_in_gamesからの参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_stages.mst_in_game_id | mst_in_games.id | インゲーム設定 | MasterDataRepository.cs:193-230 |
| mst_in_games.mst_auto_player_sequence_set_id | mst_auto_player_sequence_sets.id | 敵AI | MstInGameData.cs:29-34 |
| mst_in_games.mst_page_id | mst_pages.id | コマページ | MstInGameData.cs:59-64 |
| mst_in_games.mst_enemy_outpost_id | mst_enemy_outposts.id | 敵拠点 | MstInGameData.cs:65-70 |
| mst_in_games.mst_defense_target_id | mst_defense_targets.id | 防衛対象 | MstInGameData.cs:71-76 |
| mst_in_games.boss_mst_enemy_stage_parameter_id | mst_enemy_stage_parameters.id | ボスパラメータ | MstInGameData.cs:77-82 |

### インゲーム初期化時の参照

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| in_game → | mst_in_game_gimmick_objects | ギミックオブジェクト | InGameGimmickObjectInitializer.cs:32 |
| in_game → | mst_in_game_special_rules | 特殊ルール | StageQuestInitializer.cs:57-59 |
| in_game → | mst_in_game_special_rule_unit_statuses | 特殊ルールユニット | StageQuestInitializer.cs:61-68 |
| in_game → | mst_quest_event_bonus_schedules | イベントボーナス | InGameEventBonusUnitEffectProvider.cs:27-36 |
| in_game → | mst_unit_encyclopedia_rewards | 図鑑報酬 | InGameUnitEncyclopediaEffectProvider.cs:21 |
| in_game → | mst_unit_encyclopedia_effects | 図鑑効果 | InGameUnitEncyclopediaEffectProvider.cs:22 |

### その他のインゲーム関連

| FROM | TO | 用途 | ファイル |
|------|----|----|---------|
| mst_auto_player_sequence_elements.action | mst_enemy_stage_parameters.id | 敵AIアクション | AutoPlayerSequenceModelFactory.cs:22 |
| user_outposts.mst_artwork_id | mst_artworks.id | 拠点の原画 | OutpostInitializer.cs:62-64 |
| mst_in_game_special_rules.rule_value | mst_series.id | シリーズ制限 | StageLimitStatusModelFactory.cs:125 |

---

# データフロー図

## ステージプレイの主要なリレーション

```
[User] → [mst_stages] → [mst_quests] → [mst_events]
                      → [mst_in_games] → [mst_enemy_outposts]
                                       → [mst_defense_targets]
                                       → [mst_pages]
                                       → [mst_auto_player_sequences]
                      → [mst_stage_rewards]
                      → [opr_campaigns] → [報酬倍率適用]
```

## キャンペーンの適用フロー

```
[opr_campaigns]
  ├→ target_id_type='Quest' → [mst_quests]
  ├→ target_id_type='Series' → [mst_series]
  └→ campaign_type → [スタミナ/報酬倍率]
```

---

# 今後の調査対象

以下の領域については未調査です：
- ユニット(Unit)関連
- ガチャ(Gacha)関連
- ショップ(Shop)関連
- ミッション(Mission)関連
- PvP関連
- 降臨バトル(AdventBattle)関連
- 交換所(Exchange)関連
- その他

