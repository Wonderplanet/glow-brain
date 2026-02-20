# 生成済みマスタデータ(アウトプット)分析

## 概要
- **リリースキー**: 202601010
- **総テーブル数**: 79個
- **データが設定されているテーブル数**: 79個（全テーブルにデータあり）
- **対象イベント**: 地獄楽いいジャン祭
- **期間**: 2026-01-16 15:00:00 ~ 2026-02-16 10:59:59

---

## テーブル別分析

### 1. イベント基本情報

#### MstEvent.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_series_id, is_displayed_series_logo, is_displayed_jump_plus, start_at, end_at, asset_key, release_key
- **ID**: `event_jig_00001`
- **特徴**: 地獄楽いいジャン祭の基本情報。シリーズID `jig`（地獄楽）に紐づく
- **関連テーブル**: MstEventI18n, MstEventBonusUnit, MstEventDisplayUnit, MstQuest, MstAdventBattle

#### MstEventI18n.csv
- **レコード数**: 1件
- **主要カラム**: mst_event_id, language, name, balloon
- **特徴**: イベント名の多言語対応（ja: 「地獄楽いいジャン祭」）

#### MstEventBonusUnit.csv
- **レコード数**: 7件
- **主要カラム**: id, mst_unit_id, bonus_percentage, event_bonus_group_id
- **ID範囲**: 54 ~ 60
- **特徴**: イベント特効キャラ（bonus_percentage=20%）、グループID `raid_jig1_00001`
- **関連テーブル**: MstUnit

#### MstEventDisplayUnit.csv
- **レコード数**: 7件
- **主要カラム**: id, mst_quest_id, mst_unit_id
- **特徴**: クエスト画面に表示するキャラ（演出用）
- **関連テーブル**: MstEventDisplayUnitI18n

#### MstEventDisplayUnitI18n.csv
- **レコード数**: 7件
- **主要カラム**: mst_event_display_unit_id, language, speech_balloon_text1/2/3
- **特徴**: 吹き出しセリフ設定（例: 「ワシはっ"がらんの画眉丸"だ!!」）

---

### 2. ユニット（キャラクター）関連

#### MstUnit.csv
- **レコード数**: 4件
- **主要カラム**: id, fragment_mst_item_id, role_type, color, rarity, mst_series_id, sort_order, etc.
- **ID一覧**:
  - `chara_jig_00401` - UR, Technical, Colorless（賊王 亜左 弔兵衛）
  - `chara_jig_00501` - SSR, Support, Green
  - `chara_jig_00601` - SR, Defense, Blue（民谷 巌鉄斎）
  - `chara_jig_00701` - SR, Special, Colorless（メイ）
- **特徴**: 地獄楽シリーズ（mst_series_id=jig）のキャラ4体
- **関連テーブル**: MstUnitI18n, MstUnitAbility, MstAttack, MstSpecialAttackI18n, MstItem（欠片）

#### MstUnitI18n.csv
- **レコード数**: 4件
- **主要カラム**: mst_unit_id, language, name, description, detail
- **特徴**: キャラ名・説明の多言語対応

#### MstUnitAbility.csv
- **レコード数**: 4件
- **主要カラム**: id, mst_ability_id, ability_parameter1/2/3
- **ID一覧**:
  - `ability_jig_00401_01` - ダメージカット（HP70%以上で40%カット）
  - `ability_jig_00401_02` - 体力吸収（50%）
  - `ability_jig_00601_01` - スピードUP（50%）
  - `ability_jig_00701_01` - 攻撃力UP（HP50%以下時に40%UP）
- **関連テーブル**: MstAbility

#### MstUnitSpecificRankUp.csv
- **レコード数**: 12件
- **主要カラム**: id, mst_unit_id, rank, require_level, amount, unit_memory_amount, etc.
- **特徴**: キャラのランクアップ素材（`chara_jig_00601`, `chara_jig_00701`のみ設定）

---

### 3. アビリティ関連

#### MstAbility.csv
- **レコード数**: 2件
- **主要カラム**: id, ability_type, release_key, asset_key
- **ID一覧**:
  - `ability_attack_power_up_by_hp_percentage_less` - HP条件攻撃力UP
  - `ability_damage_cut_by_hp_percentage_over` - HP条件ダメージカット
- **特徴**: 条件付きパッシブスキル定義

#### MstAbilityI18n.csv
- **レコード数**: 2件
- **主要カラム**: mst_ability_id, language, description, filter_title
- **特徴**: アビリティ説明文（例: 「体力{1}%以下時に攻撃を{0}%UP」）

---

### 4. 攻撃アクション関連

#### MstAttack.csv
- **レコード数**: 117件
- **主要カラム**: id, mst_unit_id, unit_grade, attack_kind, killer_colors, action_frames, etc.
- **ID範囲**: `chara_jig_00401_Normal_00000` ~ `chara_jig_00701_Special_00003`
- **特徴**: 通常攻撃（Normal）と必殺技（Special）の定義。グレード別（0~9）に複数定義

#### MstAttackElement.csv
- **レコード数**: 152件
- **主要カラム**: id, mst_attack_id, sort_order, attack_type, damage_type, power_parameter, effect_type, etc.
- **特徴**: 攻撃の詳細設定（ダメージ計算、エフェクト、範囲、対象など）

#### MstAttackI18n.csv
- **レコード数**: 117件
- **主要カラム**: mst_attack_id, language, description, grade_description
- **特徴**: 攻撃説明文（多くは空欄）

#### MstSpecialAttackI18n.csv
- **レコード数**: 4件
- **主要カラム**: mst_unit_id, language, name
- **特徴**: 必殺技名（例: 「強イモ弱イモ ゼンブダイジ」）

#### MstSpecialRoleLevelUpAttackElement.csv
- **レコード数**: 5件
- **主要カラム**: id, mst_attack_element_id, min/max_power_parameter, min/max_effective_count, etc.
- **特徴**: SpecialRoleタイプのキャラ（メイ）の成長パラメータ

#### MstSpeechBalloonI18n.csv
- **レコード数**: 8件
- **主要カラム**: mst_unit_id, language, condition_type, balloon_type, text
- **特徴**: バトル中の吹き出しセリフ（必殺技チャージ時など）

---

### 5. クエスト関連

#### MstQuest.csv
- **レコード数**: 5件
- **主要カラム**: id, quest_type, mst_event_id, sort_order, asset_key, start_date, end_date, quest_group, difficulty
- **ID一覧**:
  - `quest_event_jig1_charaget01` - キャラ入手クエスト（メイ）
  - `quest_event_jig1_challenge01` - チャレンジクエスト
  - `quest_event_jig1_charaget02` - キャラ入手クエスト（ブートト）
  - `quest_event_jig1_savage` - 修羅クエスト
  - `quest_event_jig1_1day` - 1日クエスト
- **特徴**: 全てevent_jig_00001に紐づく
- **関連テーブル**: MstQuestI18n, MstStage, MstQuestBonusUnit, MstQuestEventBonusSchedule

#### MstQuestI18n.csv
- **レコード数**: 5件
- **主要カラム**: mst_quest_id, language, name, category_name, flavor_text
- **特徴**: クエスト名（例: 「必ず生きて帰る」）

#### MstQuestBonusUnit.csv
- **レコード数**: 8件
- **主要カラム**: id, mst_quest_id, mst_unit_id, coin_bonus_rate
- **ID範囲**: 58 ~ 65
- **特徴**: クエスト特効キャラ（コインボーナス15%）
- **期間**: 2026-01-16 ~ 2026-02-16

#### MstQuestEventBonusSchedule.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_quest_id, event_bonus_group_id, start_at, end_at
- **特徴**: 降臨バトル（quest_raid_jig1_00001）の特効スケジュール

---

### 6. ステージ関連

#### MstStage.csv
- **レコード数**: 20件
- **主要カラム**: id, mst_quest_id, mst_in_game_id, stage_number, recommended_level, cost_stamina, exp, coin, etc.
- **ID範囲**: `event_jig1_1day_00001` ~ `event_jig1_challenge01_00005`
- **特徴**: 各クエストに1~5ステージ設定
- **関連テーブル**: MstStageI18n, MstStageEventReward, MstStageEventSetting, MstStageClearTimeReward, MstStageEndCondition

#### MstStageI18n.csv
- **レコード数**: 20件
- **主要カラム**: mst_stage_id, language, name, category_name
- **特徴**: ステージ名（例: 「本能が告げている 危険だと」）

#### MstStageEventReward.csv
- **レコード数**: 70件
- **主要カラム**: id, mst_stage_id, reward_category, resource_type, resource_id, resource_amount, percentage, sort_order
- **ID範囲**: 539 ~ 608
- **特徴**: 初回クリア報酬（FirstClear）、ランダム報酬（Random）など
- **報酬カテゴリ**: FirstClear, Random

#### MstStageEventSetting.csv
- **レコード数**: 20件
- **主要カラム**: id, mst_stage_id, reset_type, clearable_count, ad_challenge_count, start_at, end_at, background_asset_key
- **ID範囲**: 162 ~ 181
- **特徴**: ステージのリセット設定（Daily/None）、挑戦回数、背景アセット

#### MstStageClearTimeReward.csv
- **レコード数**: 21件
- **主要カラム**: id, mst_stage_id, upper_clear_time_ms, resource_type, resource_id, resource_amount
- **特徴**: タイムアタック報酬（140秒以内クリアで無償ダイヤ20個など）

#### MstStageEndCondition.csv
- **レコード数**: 3件
- **主要カラム**: id, mst_stage_id, stage_end_type, condition_type, condition_value1/2
- **特徴**: ステージ終了条件（降臨バトルは120秒タイムオーバー、他は敵全滅）

---

### 7. 降臨バトル（レイドバトル）関連

#### MstAdventBattle.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_event_id, mst_in_game_id, asset_key, advent_battle_type, initial_battle_point, score_addition_type, etc.
- **ID**: `quest_raid_jig1_00001`
- **特徴**: スコアチャレンジ型レイド、初期BP500、スコア加算係数0.07
- **期間**: 2026-01-23 ~ 2026-01-29
- **関連テーブル**: MstAdventBattleI18n, MstAdventBattleClearReward, MstAdventBattleRank, MstAdventBattleReward, MstAdventBattleRewardGroup

#### MstAdventBattleI18n.csv
- **レコード数**: 1件
- **主要カラム**: mst_advent_battle_id, language, name, boss_description
- **特徴**: レイド名「まるで 悪夢を見ているようだ」

#### MstAdventBattleClearReward.csv
- **レコード数**: 5件
- **主要カラム**: id, mst_advent_battle_id, reward_category, resource_type, resource_id, resource_amount, percentage
- **特徴**: クリア時ランダム報酬（メモリー、欠片など）

#### MstAdventBattleRank.csv
- **レコード数**: 16件
- **主要カラム**: id, mst_advent_battle_id, rank_type, rank_level, required_lower_score, asset_key
- **特徴**: ランク評価（Bronze/Silver/Gold/Platinum 各4レベル）、スコア閾値1000~

#### MstAdventBattleReward.csv
- **レコード数**: 120件
- **主要カラム**: id, mst_advent_battle_reward_group_id, resource_type, resource_id, resource_amount
- **特徴**: 報酬グループ別の詳細報酬

#### MstAdventBattleRewardGroup.csv
- **レコード数**: 55件
- **主要カラム**: id, mst_advent_battle_id, reward_category, condition_value
- **特徴**: 報酬カテゴリ（MaxScore/TotalScore/Rank/RankUp/FirstClear）とトリガー条件

---

### 8. インゲーム設定関連

#### MstInGame.csv
- **レコード数**: 23件
- **主要カラム**: id, mst_auto_player_sequence_id, bgm_asset_key, boss_bgm_asset_key, loop_background_asset_key, mst_page_id, mst_enemy_outpost_id, etc.
- **ID範囲**: `pvp_jig_01` ~ `raid_jig1_00001`
- **特徴**: ゲームプレイシーンの総合設定（BGM、背景、敵配置、パラメータ係数など）
- **関連テーブル**: MstInGameI18n, MstInGameSpecialRule, MstPage, MstAutoPlayerSequence

#### MstInGameI18n.csv
- **レコード数**: 23件
- **主要カラム**: mst_in_game_id, language, result_tips, description
- **特徴**: リザルトTips、説明文（PVPルール説明など）

#### MstInGameSpecialRule.csv
- **レコード数**: 22件
- **主要カラム**: id, content_type, target_id, rule_type, rule_value, start_at, end_at
- **特徴**: 期間限定特殊ルール（攻撃速度1000UP、被ダメージ減少75%など）

#### MstInGameSpecialRuleUnitStatus.csv
- **レコード数**: 1件
- **主要カラム**: id, group_id, target_type, target_value, status_parameter_type, effect_value
- **特徴**: PVP特別ルール（全キャラHP200%UP）

---

### 9. ページ・コマライン関連

#### MstPage.csv
- **レコード数**: 23件
- **主要カラム**: id, release_key
- **ID範囲**: `pvp_jig_01` ~ `raid_jig1_00001`
- **特徴**: ゲーム画面のページ定義（PVP、イベントステージなど）
- **関連テーブル**: MstKomaLine

#### MstKomaLine.csv
- **レコード数**: 58件
- **主要カラム**: id, mst_page_id, row, height, koma_line_layout_asset_key, koma1~4の設定（asset, width, effect等）
- **特徴**: 漫画風演出のコマ配置と効果設定（毒コマ、HP回復コマなど）

---

### 10. 敵キャラ・オートプレイ関連

#### MstEnemyCharacter.csv
- **レコード数**: 5件
- **主要カラム**: id, mst_series_id, asset_key, is_phantomized, is_displayed_encyclopedia
- **ID一覧**: `chara_jig_00401` ~ `chara_jig_01001`（敵バージョン）
- **特徴**: プレイアブルキャラの敵バージョン
- **関連テーブル**: MstEnemyCharacterI18n, MstEnemyStageParameter

#### MstEnemyCharacterI18n.csv
- **レコード数**: 5件
- **主要カラム**: mst_enemy_character_id, language, name, description
- **特徴**: 敵キャラ名・説明

#### MstEnemyStageParameter.csv
- **レコード数**: 50件
- **主要カラム**: id, mst_enemy_character_id, character_unit_kind, role_type, color, hp, attack_power, move_speed, drop_battle_point, etc.
- **ID範囲**: `c_jig_00001_jig1_1d1c_Normal_Colorless` ~ `enemy_jig_00601_boss`
- **特徴**: ステージごとの敵パラメータ（HP、攻撃力、移動速度など）

#### MstEnemyOutpost.csv
- **レコード数**: 21件
- **主要カラム**: id, hp, is_damage_invalidation, outpost_asset_key, artwork_asset_key
- **ID範囲**: `event_jig1_1day_00001` ~ `event_jig1_challenge01_00005`
- **特徴**: 敵拠点の設定（HP、ダメージ無効化、アートワーク連動）

#### MstAutoPlayerSequence.csv
- **レコード数**: 185件
- **主要カラム**: id, sequence_set_id, sequence_group_id, condition_type, condition_value, action_type, action_value, etc.
- **特徴**: 敵の自動行動パターン（召喚、移動、攻撃、オーラなど）

---

### 11. 原画（アートワーク）関連

#### MstArtwork.csv
- **レコード数**: 2件
- **主要カラム**: id, mst_series_id, outpost_additional_hp, asset_key, sort_order
- **ID一覧**: `artwork_event_jig_0001`, `artwork_event_jig_0002`
- **特徴**: イベント原画（拠点HP+100）
- **関連テーブル**: MstArtworkI18n, MstArtworkFragment, MstArtworkFragmentPosition

#### MstArtworkI18n.csv
- **レコード数**: 2件
- **主要カラム**: mst_artwork_id, language, name, description
- **特徴**: 原画タイトル・説明（例: 「必ず生きて帰る」）

#### MstArtworkFragment.csv
- **レコード数**: 32件
- **主要カラム**: id, mst_artwork_id, drop_group_id, drop_percentage, rarity, asset_num
- **特徴**: 原画の欠片（16個 x 2原画 = 32件）、ドロップ率100%

#### MstArtworkFragmentI18n.csv
- **レコード数**: 32件
- **主要カラム**: mst_artwork_fragment_id, language, name
- **特徴**: 欠片名（例: 「原画のかけら7」）

#### MstArtworkFragmentPosition.csv
- **レコード数**: 32件
- **主要カラム**: id, mst_artwork_fragment_id, position
- **特徴**: 欠片の配置位置（1~16）

---

### 12. マンガアニメーション関連

#### MstMangaAnimation.csv
- **レコード数**: 24件
- **主要カラム**: id, mst_stage_id, condition_type, condition_value, animation_start_delay, animation_speed, is_pause, can_skip, asset_key
- **特徴**: ステージ開始/終了/途中での漫画演出

---

### 13. ミッション関連

#### MstMissionEvent.csv
- **レコード数**: 43件
- **主要カラム**: id, mst_event_id, criterion_type, criterion_value, criterion_count, group_key, mst_mission_reward_group_id, sort_order, destination_scene
- **特徴**: イベントミッション（キャラ強化、ステージクリア、ガチャ実行など）
- **関連テーブル**: MstMissionEventI18n, MstMissionEventDependency, MstMissionReward

#### MstMissionEventI18n.csv
- **レコード数**: 43件
- **主要カラム**: mst_mission_event_id, language, description
- **特徴**: ミッション説明文（例: 「メイ をグレード2まで強化しよう」）

#### MstMissionEventDependency.csv
- **レコード数**: 39件
- **主要カラム**: id, group_id, mst_mission_event_id, unlock_order
- **特徴**: ミッション解放順序の依存関係

#### MstMissionEventDailyBonus.csv
- **レコード数**: 17件
- **主要カラム**: id, mst_mission_event_daily_bonus_schedule_id, login_day_count, mst_mission_reward_group_id, sort_order
- **特徴**: 期間限定ログインボーナス（1~17日目）

#### MstMissionEventDailyBonusSchedule.csv
- **レコード数**: 1件
- **主要カラム**: id, mst_event_id, start_at, end_at
- **特徴**: ログインボーナススケジュール（2026-01-16 ~ 2026-02-02）

#### MstMissionLimitedTerm.csv
- **レコード数**: 4件
- **主要カラム**: id, progress_group_key, criterion_type, criterion_value, criterion_count, mission_category, start_at, end_at
- **特徴**: 期間限定ミッション（降臨バトル5回挑戦など）

#### MstMissionLimitedTermI18n.csv
- **レコード数**: 4件
- **主要カラム**: mst_mission_limited_term_id, language, description
- **特徴**: 期間限定ミッション説明

#### MstMissionReward.csv
- **レコード数**: 64件
- **主要カラム**: id, group_id, resource_type, resource_id, resource_amount, sort_order
- **特徴**: ミッション報酬の詳細（ダイヤ、ガチャチケット、メモリー、コインなど）

---

### 14. アイテム関連

#### MstItem.csv
- **レコード数**: 6件
- **主要カラム**: id, type, group_type, rarity, asset_key, effect_value, sort_order, start_date, end_date
- **ID一覧**:
  - `memory_chara_jig_00601` - 民谷 巌鉄斎のメモリー（SR）
  - `memory_chara_jig_00701` - メイのメモリー（SR）
  - `memory_glo_00001` - グロー討伐隊メモリー（SR）
  - `memory_glo_00003` - サポートメモリー（R）
  - `ticket_glo_00001` - レアチケット（SSR）
  - `ticket_glo_00003` - いいジャン祭ピックアップガチャチケット（SSR）
- **関連テーブル**: MstItemI18n

#### MstItemI18n.csv
- **レコード数**: 6件
- **主要カラム**: mst_item_id, language, name, description
- **特徴**: アイテム名・説明

---

### 15. ガチャ関連

#### OprGacha.csv
- **レコード数**: 2件
- **主要カラム**: id, gacha_type, upper_group, multi_draw_count, multi_fixed_prize_count, prize_group_id, fixed_prize_group_id, start_at, end_at, gacha_priority
- **ID一覧**:
  - `Pickup_jig_001` - ピックアップガチャA（優先度66）
  - `Pickup_jig_002` - ピックアップガチャB（優先度65）
- **期間**: 2026-01-16 12:00:00 ~ 2026-02-16 10:59:59
- **関連テーブル**: OprGachaI18n, OprGachaPrize, OprGachaUpper, OprGachaUseResource, OprGachaDisplayUnitI18n

#### OprGachaI18n.csv
- **レコード数**: 2件
- **主要カラム**: opr_gacha_id, language, name, description, pickup_upper_description, fixed_prize_description, logo_asset_key
- **特徴**: ガチャ名・説明（例: 「地獄楽 いいジャン祭ピックアップガシャ A」）

#### OprGachaPrize.csv
- **レコード数**: 150件
- **主要カラム**: id, group_id, resource_type, resource_id, resource_amount, weight, pickup
- **特徴**: ガチャ排出内容（キャラ、欠片など）とピックアップフラグ

#### OprGachaUpper.csv
- **レコード数**: 2件
- **主要カラム**: id, upper_group, upper_type, count
- **特徴**: 天井設定（100回でピックアップ確定）

#### OprGachaUseResource.csv
- **レコード数**: 6件
- **主要カラム**: id, opr_gacha_id, cost_type, cost_id, cost_num, draw_count, cost_priority
- **特徴**: ガチャコスト設定（無償ダイヤ、有償ダイヤ、チケット）

#### OprGachaDisplayUnitI18n.csv
- **レコード数**: 4件
- **主要カラム**: opr_gacha_id, mst_unit_id, language, sort_order, description
- **特徴**: ガチャ画面表示キャラ説明

---

### 16. ストア・商品関連

#### MstStoreProduct.csv
- **レコード数**: 7件
- **主要カラム**: id, product_id_ios, product_id_android, product_id_webstore
- **ID範囲**: 50 ~ 56
- **特徴**: アプリ内課金商品ID（iOS/Android/Web）
- **関連テーブル**: MstStoreProductI18n, OprProduct

#### MstStoreProductI18n.csv
- **レコード数**: 7件
- **主要カラム**: mst_store_product_id, language, price_ios/android/webstore, paid_diamond_price_*
- **特徴**: 価格情報（3000円など）

#### OprProduct.csv
- **レコード数**: 7件
- **主要カラム**: id, mst_store_product_id, product_type, purchasable_count, paid_amount, display_priority, start_date, end_date
- **ID範囲**: 50 ~ 56
- **特徴**: 期間限定販売商品（いいジャン祭パックなど）
- **関連テーブル**: OprProductI18n

#### OprProductI18n.csv
- **レコード数**: 7件
- **主要カラム**: opr_product_id, language, asset_key
- **特徴**: 商品アセットキー

#### MstPack.csv
- **レコード数**: 2件
- **主要カラム**: id, product_sub_id, discount_rate, pack_type, tradable_count, cost_type, cost_amount, asset_key
- **ID一覧**: `event_item_pack_12`, `event_item_pack_13`
- **特徴**: パック商品設定
- **関連テーブル**: MstPackI18n, MstPackContent

#### MstPackI18n.csv
- **レコード数**: 2件
- **主要カラム**: mst_pack_id, language, name
- **特徴**: パック名（例: 「【お一人様1回まで購入可】いいジャン祭 開催記念パック」）

#### MstPackContent.csv
- **レコード数**: 7件
- **主要カラム**: id, mst_pack_id, resource_type, resource_id, resource_amount, is_bonus, display_order
- **特徴**: パック内容物（メモリー欠片、ガチャチケット、コインなど）

---

### 17. PVP関連

#### MstPvp.csv
- **レコード数**: 2件
- **主要カラム**: id, ranking_min_pvp_rank_class, max_daily_challenge_count, item_challenge_count, mst_in_game_id, initial_battle_point
- **ID一覧**: `2026004`, `2026005`
- **特徴**: PVPシーズン設定、挑戦回数、初期BP1000
- **関連テーブル**: MstPvpI18n

#### MstPvpI18n.csv
- **レコード数**: 2件
- **主要カラム**: mst_pvp_id, language, name, description
- **特徴**: PVPルール説明（3段ステージ、毒コマ、特別ルールなど）

---

### 18. バナー関連

#### MstHomeBanner.csv
- **レコード数**: 3件
- **主要カラム**: id, destination, destination_path, asset_key, start_at, end_at, sort_order
- **ID範囲**: 23 ~ 25
- **特徴**: ホーム画面バナー（イベント、ガチャへの導線）

---

### 19. エンブレム関連

#### MstEmblem.csv
- **レコード数**: 7件
- **主要カラム**: id, emblemType, mstSeriesId, assetKey
- **ID一覧**: `emblem_event_jig_00001` ~ `emblem_event_jig_00007`
- **特徴**: イベントエンブレム（称号）
- **関連テーブル**: MstEmblemI18n

#### MstEmblemI18n.csv
- **レコード数**: 7件
- **主要カラム**: mst_emblem_id, language, name, description
- **特徴**: エンブレム名・説明（例: 「神仙郷」「仙薬探しのため...」）

---

## プレフィックス別グループ

### Mstイベント関連（Event系）
- **MstEvent** - イベント基本情報
- **MstEventI18n** - イベント多言語
- **MstEventBonusUnit** - イベント特効キャラ
- **MstEventDisplayUnit** - イベント表示キャラ
- **MstEventDisplayUnitI18n** - 表示キャラ吹き出し

### Mstクエスト関連（Quest/Stage系）
- **MstQuest** - クエスト定義
- **MstQuestI18n** - クエスト多言語
- **MstQuestBonusUnit** - クエスト特効キャラ
- **MstQuestEventBonusSchedule** - 特効スケジュール
- **MstStage** - ステージ定義
- **MstStageI18n** - ステージ多言語
- **MstStageEventReward** - ステージ報酬
- **MstStageEventSetting** - ステージ設定
- **MstStageClearTimeReward** - タイムアタック報酬
- **MstStageEndCondition** - 終了条件

### Mst降臨バトル関連（AdventBattle系）
- **MstAdventBattle** - 降臨バトル基本情報
- **MstAdventBattleI18n** - 降臨バトル多言語
- **MstAdventBattleClearReward** - クリア報酬
- **MstAdventBattleRank** - ランク評価
- **MstAdventBattleReward** - 報酬詳細
- **MstAdventBattleRewardGroup** - 報酬グループ

### Mstユニット関連（Unit系）
- **MstUnit** - ユニット基本情報
- **MstUnitI18n** - ユニット多言語
- **MstUnitAbility** - ユニットアビリティ
- **MstUnitSpecificRankUp** - ランクアップ素材

### Mstアビリティ関連（Ability系）
- **MstAbility** - アビリティ定義
- **MstAbilityI18n** - アビリティ多言語

### Mst攻撃関連（Attack系）
- **MstAttack** - 攻撃定義
- **MstAttackElement** - 攻撃要素
- **MstAttackI18n** - 攻撃多言語
- **MstSpecialAttackI18n** - 必殺技多言語
- **MstSpecialRoleLevelUpAttackElement** - 特殊ロール成長
- **MstSpeechBalloonI18n** - 吹き出しセリフ

### Mst原画関連（Artwork系）
- **MstArtwork** - 原画基本情報
- **MstArtworkI18n** - 原画多言語
- **MstArtworkFragment** - 原画欠片
- **MstArtworkFragmentI18n** - 欠片多言語
- **MstArtworkFragmentPosition** - 欠片配置

### Mstインゲーム関連（InGame系）
- **MstInGame** - インゲーム設定
- **MstInGameI18n** - インゲーム多言語
- **MstInGameSpecialRule** - 特殊ルール
- **MstInGameSpecialRuleUnitStatus** - 特殊ルールステータス
- **MstPage** - ページ定義
- **MstKomaLine** - コマライン

### Mst敵関連（Enemy系）
- **MstEnemyCharacter** - 敵キャラ
- **MstEnemyCharacterI18n** - 敵キャラ多言語
- **MstEnemyStageParameter** - 敵ステージパラメータ
- **MstEnemyOutpost** - 敵拠点
- **MstAutoPlayerSequence** - 自動行動パターン

### Mstミッション関連（Mission系）
- **MstMissionEvent** - イベントミッション
- **MstMissionEventI18n** - イベントミッション多言語
- **MstMissionEventDependency** - ミッション依存関係
- **MstMissionEventDailyBonus** - 期間限定ログボ
- **MstMissionEventDailyBonusSchedule** - ログボスケジュール
- **MstMissionLimitedTerm** - 期間限定ミッション
- **MstMissionLimitedTermI18n** - 期間限定ミッション多言語
- **MstMissionReward** - ミッション報酬

### MstアイテムUI関連
- **MstItem** - アイテム定義
- **MstItemI18n** - アイテム多言語
- **MstHomeBanner** - ホームバナー
- **MstEmblem** - エンブレム
- **MstEmblemI18n** - エンブレム多言語

### Mstその他
- **MstMangaAnimation** - マンガアニメーション

### Mst PVP関連
- **MstPvp** - PVP設定
- **MstPvpI18n** - PVP多言語

### Opr ガチャ関連（Gacha系）
- **OprGacha** - ガチャ基本情報
- **OprGachaI18n** - ガチャ多言語
- **OprGachaPrize** - ガチャ排出
- **OprGachaUpper** - 天井設定
- **OprGachaUseResource** - ガチャコスト
- **OprGachaDisplayUnitI18n** - ガチャ表示キャラ

### Mst/Opr 商品関連（Product/Pack/Store系）
- **MstStoreProduct** - ストア商品（IAP ID）
- **MstStoreProductI18n** - ストア商品多言語
- **OprProduct** - 期間限定商品
- **OprProductI18n** - 期間限定商品多言語
- **MstPack** - パック商品
- **MstPackI18n** - パック多言語
- **MstPackContent** - パック内容

---

## 採番ルールの推測

### イベントID
- **パターン**: `event_{series}_{number}`
- **例**: `event_jig_00001`
- **シリーズコード**: `jig` = 地獄楽

### キャラID
- **パターン**: `chara_{series}_{number}`
- **範囲**:
  - `chara_jig_00401` ~ `chara_jig_01001`（地獄楽）
  - 00401, 00501, 00601, 00701（プレイアブル）
  - 00001, 00201, 00801, 00901, 01001（敵のみ）
- **特徴**: プレイアブルと敵で異なる番号体系

### クエストID
- **パターン**: `quest_event_{series}{event_number}_{quest_type}{number}`
- **例**:
  - `quest_event_jig1_charaget01` - キャラ入手クエスト
  - `quest_event_jig1_challenge01` - チャレンジクエスト
  - `quest_event_jig1_savage` - 修羅クエスト
  - `quest_event_jig1_1day` - 1日クエスト
  - `quest_raid_jig1_00001` - 降臨バトル

### ステージID
- **パターン**: `{quest_id}_{stage_number}`
- **例**: `event_jig1_1day_00001` ~ `event_jig1_1day_00007`（7ステージ）

### ガチャID（Opr）
- **パターン**: `Pickup_{series}_{number}`
- **例**: `Pickup_jig_001`, `Pickup_jig_002`

### 商品ID（Opr）
- **パターン**: 数値ID
- **範囲**: 50 ~ 56
- **特徴**: 連番、イベントパック用

### パックID（Mst）
- **パターン**: `event_item_pack_{number}`
- **例**: `event_item_pack_12`, `event_item_pack_13`

### アイテムID
- **パターン**:
  - `memory_{target}_{number}` - メモリー
  - `ticket_{system}_{number}` - チケット
  - `piece_{target}_{number}` - 欠片（unitのfragment_mst_item_id）
- **例**:
  - `memory_chara_jig_00601` - キャラメモリー
  - `memory_glo_00001` - システムメモリー
  - `ticket_glo_00003` - ガチャチケット
  - `piece_jig_00401` - キャラ欠片

### 原画ID
- **パターン**: `artwork_event_{series}_{number}`
- **例**: `artwork_event_jig_0001`, `artwork_event_jig_0002`

### 原画欠片ID
- **パターン**: `artwork_fragment_event_{series}_{artwork_number}{suffix_number}`
- **例**: `artwork_fragment_event_jig_00001` ~ `artwork_fragment_event_jig_00016`（16個/原画）

### バナーID
- **パターン**: 数値ID
- **範囲**: 23 ~ 25

### エンブレムID
- **パターン**: `emblem_event_{series}_{number}`
- **例**: `emblem_event_jig_00001` ~ `emblem_event_jig_00007`

### ミッションID（MstMissionEvent）
- **パターン**: `{event_id}_{number}`
- **例**: `event_jig_00001_1` ~ `event_jig_00001_43`

### ミッションID（MstMissionLimitedTerm）
- **パターン**: `limited_term_{number}`
- **範囲**: 29 ~ 32

### 報酬グループID
- **パターン**:
  - `{resource_prefix}_{number}` - 汎用
  - `{quest_id}_reward_group_{variant}_{number}` - クエスト固有
- **例**:
  - `jig_00001_event_reward_01`
  - `jig_00001_limited_term_1`
  - `quest_raid_jig1_reward_group_00001_01`

### アビリティID
- **パターン**:
  - `ability_{effect_name}` - アビリティ定義
  - `ability_{series}_{chara_number}_{ability_number}` - ユニットアビリティ
- **例**:
  - `ability_attack_power_up_by_hp_percentage_less`
  - `ability_jig_00401_01`, `ability_jig_00401_02`

### 攻撃ID
- **パターン**: `{unit_id}_{attack_kind}_{number}`
- **例**:
  - `chara_jig_00401_Normal_00000`
  - `chara_jig_00401_Special_00000`

### インゲームID
- **パターン**:
  - `{content_type}_{series}_{number}` - PVP
  - `{quest_type}_{series}{event_number}_{number}` - イベントクエスト
- **例**:
  - `pvp_jig_01`
  - `event_jig1_1day_00001`
  - `raid_jig1_00001`

### 敵ステージパラメータID
- **パターン**:
  - `c_{series}_{enemy_number}_{stage_variant}_{unit_kind}_{color}` - 通常敵
  - `enemy_{series}_{enemy_number}_{variant}` - ボス敵
- **例**:
  - `c_jig_00001_jig1_1d1c_Normal_Colorless`
  - `enemy_jig_00601_boss`

---

## 次のタスクへの示唆

### 1. 設計書とテーブルの対応推測

#### イベント企画書 → イベント基本設定
- **MstEvent** - イベント基本情報（期間、シリーズID）
- **MstEventI18n** - イベント名、バナーテキスト
- **MstHomeBanner** - ホーム画面バナー

#### キャラクター設計 → ユニット設定
- **MstUnit** - キャラステータス（HP、攻撃力、ロール、レアリティ）
- **MstUnitI18n** - キャラ名、説明
- **MstUnitAbility** - パッシブスキル
- **MstAttack** - 通常攻撃/必殺技
- **MstAttackElement** - 攻撃詳細（範囲、威力、エフェクト）
- **MstSpecialAttackI18n** - 必殺技名
- **MstSpeechBalloonI18n** - バトル中セリフ
- **MstEnemyCharacter** - 敵バージョン
- **MstEnemyCharacterI18n** - 敵キャラ名
- **MstEnemyStageParameter** - ステージ別敵パラメータ
- **MstItem** - メモリー/欠片アイテム
- **MstItemI18n** - アイテム名

#### クエスト設計 → クエスト/ステージ設定
- **MstQuest** - クエスト定義（タイプ、難易度、期間）
- **MstQuestI18n** - クエスト名、説明
- **MstStage** - ステージ定義（推奨レベル、スタミナ、報酬）
- **MstStageI18n** - ステージ名
- **MstStageEventReward** - 報酬設定（初回クリア、ランダム）
- **MstStageEventSetting** - リセット設定、挑戦回数
- **MstStageClearTimeReward** - タイムアタック報酬
- **MstInGame** - インゲーム設定（BGM、背景、敵配置）
- **MstInGameI18n** - Tips、説明
- **MstAutoPlayerSequence** - 敵行動パターン
- **MstEnemyOutpost** - 敵拠点設定
- **MstPage** - ページ定義
- **MstKomaLine** - コマ配置とエフェクト
- **MstMangaAnimation** - 漫画演出
- **MstQuestBonusUnit** - 特効キャラ設定
- **MstEventBonusUnit** - イベント特効キャラ
- **MstEventDisplayUnit** - 表示キャラ
- **MstEventDisplayUnitI18n** - 吹き出しセリフ
- **MstInGameSpecialRule** - 特殊ルール（期間限定バフ/デバフ）

#### 降臨バトル設計 → レイドバトル設定
- **MstAdventBattle** - 降臨バトル基本情報（タイプ、スコア設定）
- **MstAdventBattleI18n** - 名前、説明
- **MstAdventBattleClearReward** - クリア報酬
- **MstAdventBattleRank** - ランク評価基準
- **MstAdventBattleReward** - 報酬詳細
- **MstAdventBattleRewardGroup** - 報酬カテゴリ（最高スコア、累計スコア、ランク達成等）
- **MstQuestEventBonusSchedule** - 特効スケジュール

#### ガチャ設計 → ガチャ設定
- **OprGacha** - ガチャ基本情報（タイプ、期間、回数）
- **OprGachaI18n** - ガチャ名、説明、バナー
- **OprGachaPrize** - 排出内容とピックアップ
- **OprGachaUpper** - 天井設定
- **OprGachaUseResource** - コスト設定（無償/有償ダイヤ、チケット）
- **OprGachaDisplayUnitI18n** - 表示キャラ説明

#### ミッション設計 → ミッション設定
- **MstMissionEvent** - イベントミッション（条件、報酬グループ）
- **MstMissionEventI18n** - ミッション説明
- **MstMissionEventDependency** - ミッション依存関係
- **MstMissionEventDailyBonus** - ログインボーナス
- **MstMissionEventDailyBonusSchedule** - ログボスケジュール
- **MstMissionLimitedTerm** - 期間限定ミッション
- **MstMissionLimitedTermI18n** - 期間限定ミッション説明
- **MstMissionReward** - 報酬詳細

#### 原画設計 → 原画/コレクション設定
- **MstArtwork** - 原画基本情報
- **MstArtworkI18n** - 原画タイトル、説明
- **MstArtworkFragment** - 欠片定義（ドロップ率）
- **MstArtworkFragmentI18n** - 欠片名
- **MstArtworkFragmentPosition** - 欠片配置

#### ストア/パック設計 → 商品設定
- **MstStoreProduct** - IAP商品ID
- **MstStoreProductI18n** - 価格情報
- **OprProduct** - 期間限定商品
- **OprProductI18n** - 商品アセット
- **MstPack** - パック定義
- **MstPackI18n** - パック名
- **MstPackContent** - パック内容物

#### PVP設計 → PVP設定
- **MstPvp** - PVPシーズン設定
- **MstPvpI18n** - PVPルール説明
- **MstInGameSpecialRuleUnitStatus** - PVP特別ルール（ステータス補正）

#### エンブレム設計 → 称号設定
- **MstEmblem** - エンブレム定義
- **MstEmblemI18n** - エンブレム名、説明

---

### 2. 特に複雑な設定が必要なテーブル

#### 高複雑度（慎重に設定すべき）
1. **MstAttack** / **MstAttackElement** - 攻撃モーションと詳細設定（152要素）、グレード別成長
2. **MstAutoPlayerSequence** - 敵の自動行動パターン（185シーケンス）
3. **MstInGame** - インゲーム総合設定（BGM、背景、敵配置、パラメータ係数など）
4. **MstKomaLine** - コマ配置とエフェクト（複数カラムの組み合わせ）
5. **MstAdventBattleRewardGroup** / **MstAdventBattleReward** - 報酬カテゴリと詳細（複雑な条件分岐）
6. **MstMissionEventDependency** - ミッション依存関係（解放順序）
7. **OprGachaPrize** - ガチャ排出（重み付け、ピックアップフラグ）

#### 中複雑度（設計書から明確に定義すべき）
1. **MstUnit** - キャラステータス（多数のパラメータ）
2. **MstStage** / **MstStageEventReward** - ステージ報酬設定
3. **MstEnemyStageParameter** - ステージ別敵パラメータ
4. **MstMissionEvent** - イベントミッション条件
5. **MstInGameSpecialRule** - 期間限定特殊ルール

#### 低複雑度（パターン化しやすい）
1. **各I18nテーブル** - 多言語対応（現状jaのみ）
2. **MstArtworkFragment** / **MstArtworkFragmentPosition** - 欠片定義（定型パターン）
3. **MstItem** - アイテム定義（単純な設定）
4. **MstHomeBanner** - バナー設定（単純なリンク）

---

### 3. データ作成時の注意点

#### ID採番の一貫性
- **イベント番号**: `jig1`（地獄楽1回目）という接頭辞を徹底
- **キャラ番号**: 既存体系（00401, 00501, 00601, 00701）との整合性
- **リリースキー**: 全テーブルで`202601010`を統一

#### 外部キー整合性
- `mst_event_id`が`event_jig_00001`に統一されているか
- `mst_unit_id`が実在するキャラIDを参照しているか
- `mst_item_id`（欠片、メモリー）が実在するアイテムIDか
- `mst_quest_id` → `mst_stage_id`の親子関係
- `mst_attack_id` → `mst_attack_element_id`の親子関係
- `mst_advent_battle_reward_group_id` → `mst_advent_battle_reward_id`の親子関係

#### 期間設定の整合性
- イベント全体期間: 2026-01-16 15:00 ~ 2026-02-16 10:59
- 降臨バトル期間: 2026-01-23 15:00 ~ 2026-01-29 14:59（イベント期間内）
- ガチャ期間: 2026-01-16 12:00 ~ 2026-02-16 10:59（イベントより3時間早い開始）
- ログボ期間: 2026-01-16 15:00 ~ 2026-02-02 03:59（イベントより短い）

#### 多言語設定
- 現状は`ja`のみ設定
- 全I18nテーブルで対応する親レコードと同数のレコードが必要

#### ENABLE列
- 全レコードで`e`（有効）が設定されている
- 無効化する場合は`d`に変更（現在は使用例なし）

#### __NULL__の扱い
- NULLを明示的に表現する際に使用
- 例: `unlock_condition_type`が`None`で`unlock_duration_hours`が`__NULL__`

---

### 4. 今回のリリースキー202601010の特徴

- **初回イベント**: 地獄楽シリーズの最初のイベント
- **キャラ数**: プレイアブル4体（UR1, SSR1, SR2）+ 敵5体
- **クエスト数**: 5クエスト（キャラ入手x2, チャレンジ, 修羅, 1日）
- **ステージ数**: 20ステージ（各クエスト複数ステージ）
- **降臨バトル**: 1つ（スコアチャレンジ型）
- **ガチャ**: 2つ（ピックアップA/B）
- **原画**: 2枚（各16欠片）
- **ミッション**: イベント43個 + 期間限定4個
- **パック商品**: 2つ
- **エンブレム**: 7種類

---

## まとめ

このリリースキー202601010では、**地獄楽いいジャン祭**という大規模イベントのために79個のテーブル全てにデータが設定されています。

イベント、クエスト、降臨バトル、ガチャ、ミッション、原画、商品など、あらゆる要素が網羅的に定義されており、相互に外部キーで緊密に連携しています。

特に、キャラクターの攻撃アクション定義（MstAttack/MstAttackElement）、敵の自動行動パターン（MstAutoPlayerSequence）、降臨バトルの報酬体系（MstAdventBattleReward系）など、複雑な設定が必要なテーブルが多数含まれています。

次のタスクでは、これらのアウトプットに対応する設計書・運営仕様書（インプット）を調査し、どの仕様書からどのテーブルが生成されるかのマッピングを明確化する必要があります。
