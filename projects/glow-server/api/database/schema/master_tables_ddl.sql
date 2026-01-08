-- =============================================================================
-- Master Tables DDL (mst, mng)
-- =============================================================================


-- =============================================================================
-- Database: mst (mst)
-- =============================================================================
-- Table: mst_abilities
CREATE TABLE `mst_abilities` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `ability_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'アビリティタイプ',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'アセットキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット特性・コマ効果の設定';

-- Table: mst_abilities_i18n
CREATE TABLE `mst_abilities_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_ability_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstAbilityId',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明文',
  `filter_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ユニットソートフィルタータイトル',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット特性・コマ効果の多言語設定';

-- Table: mst_advent_battle_clear_rewards
CREATE TABLE `mst_advent_battle_clear_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battle.id',
  `reward_category` enum('Always','FirstClear','Random') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬カテゴリー',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬のID(アイテムだったらアイテムのID、コインやプリズムなら空)',
  `resource_amount` int unsigned DEFAULT NULL COMMENT '配布される報酬の量',
  `percentage` int unsigned NOT NULL DEFAULT '1' COMMENT '出現比重',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_advent_battle_id_index` (`mst_advent_battle_id`),
  KEY `mst_advent_battle_clear_rewards_mst_advent_battle_id_index` (`mst_advent_battle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルのクリア報酬設定';

-- Table: mst_advent_battle_ranks
CREATE TABLE `mst_advent_battle_ranks` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battles.id',
  `rank_type` enum('Bronze','Silver','Gold','Master') COLLATE utf8mb4_bin NOT NULL COMMENT '降臨バトルランクタイプ',
  `rank_level` tinyint unsigned NOT NULL COMMENT 'ランクレベル',
  `required_lower_score` bigint unsigned NOT NULL COMMENT 'このランクタイプとレベル到達に必要な最低スコア',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'アセットキー',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルのランク設定';

-- Table: mst_advent_battle_reward_groups
CREATE TABLE `mst_advent_battle_reward_groups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battles.id',
  `reward_category` enum('MaxScore','Ranking','Rank','RaidTotalScore') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬カテゴリー',
  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '報酬条件値',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルの報酬情報をグルーピング';

-- Table: mst_advent_battle_rewards
CREATE TABLE `mst_advent_battle_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_advent_battle_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battle_reward_groups.id',
  `resource_type` enum('Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数量',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルの報酬設定';

-- Table: mst_advent_battles
CREATE TABLE `mst_advent_battles` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_events.id',
  `mst_in_game_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_in_games.id',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アセットキー',
  `advent_battle_type` enum('ScoreChallenge','Raid') COLLATE utf8mb4_bin NOT NULL COMMENT '降臨バトルタイプ',
  `initial_battle_point` int NOT NULL DEFAULT '0' COMMENT 'インゲーム開始時のリーダーP',
  `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
  `event_bonus_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_event_bonus_units.event_bonus_group_id',
  `challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の挑戦可能回数',
  `ad_challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告視聴での挑戦可能回数',
  `display_mst_unit_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所1に表示するキャラ',
  `display_mst_unit_id2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所2に表示するキャラ',
  `display_mst_unit_id3` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所3に表示するキャラ',
  `exp` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得リーダーEXP',
  `coin` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得コイン',
  `start_at` timestamp NOT NULL COMMENT '降臨バトル開始日',
  `end_at` timestamp NOT NULL COMMENT '降臨バトル終了日',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `score_addition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'AllEnemiesAndOutPost' COMMENT '降臨バトルスコア加算タイプ(スコアチャレンジ、レイド)',
  `score_additional_coef` decimal(5,3) NOT NULL DEFAULT '0.000' COMMENT '降臨バトルスコア加算係数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルの基本設定';

-- Table: mst_advent_battles_i18n
CREATE TABLE `mst_advent_battles_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battles.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '名前',
  `boss_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '降臨バトルボス説明文',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='降臨バトルの基本設定の多言語設定';

-- Table: mst_api_actions
CREATE TABLE `mst_api_actions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `api_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIパス',
  `through_app` tinyint unsigned NOT NULL COMMENT 'アプリバージョンのチェック（強制アプデなど）をスキップするか',
  `through_master` tinyint unsigned NOT NULL COMMENT 'マスターデータのバージョンチェックをスキップするか',
  `through_date` tinyint unsigned NOT NULL COMMENT '日跨ぎチェックをスキップするか',
  `through_asset` tinyint unsigned NOT NULL COMMENT 'アセットデータのバージョンチェックをスキップするか',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  `resource` json DEFAULT NULL COMMENT 'API追加情報',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_api_actions_api_path_unique` (`api_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API動作設定';

-- Table: mst_artwork_fragment_positions
CREATE TABLE `mst_artwork_fragment_positions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_artwork_fragment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
  `position` smallint unsigned DEFAULT NULL COMMENT '表示位置(1~16)',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけらの表示位置設定';

-- Table: mst_artwork_fragments
CREATE TABLE `mst_artwork_fragments` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_artwork_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
  `drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ステージのドロップ単位(非ドロップはNULL)',
  `drop_percentage` smallint unsigned DEFAULT NULL COMMENT 'ドロップ率(非ドロップはNULL)',
  `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'R' COMMENT 'レアリティ',
  `asset_num` int NOT NULL DEFAULT '0' COMMENT 'アセット番号',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら設定';

-- Table: mst_artwork_fragments_i18n
CREATE TABLE `mst_artwork_fragments_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_artwork_fragment_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画のかけら名',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_artwork_fragment_id_language` (`mst_artwork_fragment_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら名などの設定';

-- Table: mst_artworks
CREATE TABLE `mst_artworks` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_series_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
  `outpost_additional_hp` bigint unsigned NOT NULL COMMENT '完成時にゲートに加算するHP',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画画像アセット',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画の設定';

-- Table: mst_artworks_i18n
CREATE TABLE `mst_artworks_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_artwork_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画名',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原画の説明文',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_artwork_id_language` (`mst_artwork_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画名などの設定';

-- Table: mst_attack_elements
CREATE TABLE `mst_attack_elements` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_attack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_attacks.id',
  `sort_order` int NOT NULL COMMENT '表示順',
  `attack_delay` int NOT NULL COMMENT '攻撃が発生するまでの時間',
  `attack_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `range_start_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '攻撃範囲開始タイプ',
  `range_start_parameter` double(8,2) NOT NULL COMMENT '攻撃範囲開始値',
  `range_end_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '攻撃範囲終了タイプ',
  `range_end_parameter` double(8,2) NOT NULL COMMENT '攻撃範囲終了値',
  `max_target_count` int NOT NULL COMMENT '攻撃対象最大数',
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` enum('All','Character','Outpost') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象タイプ',
  `target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象属性',
  `target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ロールタイプ',
  `damage_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'ダメージタイプ',
  `hit_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT '命中効果タイプ',
  `hit_parameter1` int unsigned NOT NULL DEFAULT '0' COMMENT '命中効果値1',
  `hit_parameter2` int unsigned NOT NULL DEFAULT '0' COMMENT '命中効果値2',
  `hit_effect_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '命中時エフェクトID',
  `is_hit_stop` tinyint NOT NULL DEFAULT '0' COMMENT 'ヒットストップするか',
  `probability` int NOT NULL COMMENT '確率',
  `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '攻撃力増減タイプ',
  `power_parameter` int NOT NULL COMMENT '攻撃力増減値',
  `effect_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT '攻撃効果タイプ',
  `effective_count` int NOT NULL COMMENT '攻撃効果回数',
  `effective_duration` int NOT NULL COMMENT '攻撃効果間隔',
  `effect_parameter` double NOT NULL DEFAULT '0' COMMENT '攻撃効果値',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='インゲーム向けユニットの攻撃データ';

-- Table: mst_attack_hit_effects
CREATE TABLE `mst_attack_hit_effects` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `onomatopoeia1_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '擬音語アセットキー1',
  `onomatopoeia2_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '擬音語アセットキー2',
  `onomatopoeia3_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '擬音語アセットキー3',
  `sound_effect_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'SEアセットキー',
  `killer_sound_effect_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '弱点攻撃SEアセットキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ユニットの攻撃ヒット演出設定';

-- Table: mst_attacks
CREATE TABLE `mst_attacks` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst.units.id',
  `unit_grade` int NOT NULL COMMENT 'ユニットグレード',
  `attack_kind` enum('Normal','Special','Appearance') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '攻撃種別',
  `killer_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '弱点対象属性',
  `killer_percentage` int NOT NULL DEFAULT '0' COMMENT '弱点割合',
  `action_frames` int NOT NULL COMMENT 'アクションフレーム数',
  `attack_delay` int NOT NULL COMMENT '攻撃が発生するまでの時間',
  `next_attack_interval` int NOT NULL COMMENT '次回攻撃間隔',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニットの攻撃データ';

-- Table: mst_attacks_i18n
CREATE TABLE `mst_attacks_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_attack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_attacks.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '攻撃説明',
  `grade_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'グレード説明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニットの攻撃データの多言語設定';

-- Table: mst_auto_player_sequences
CREATE TABLE `mst_auto_player_sequences` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `sequence_set_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'シーケンスセット用のID',
  `sequence_element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '順序要素ID',
  `sequence_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '順序グループID',
  `priority_sequence_element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '優先順序要素ID',
  `condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '条件タイプ',
  `condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '条件値',
  `action_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT 'アクション種別',
  `action_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'アクション値',
  `action_value2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アクション値2',
  `summon_animation_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '召喚アニメーションタイプ',
  `summon_count` int NOT NULL DEFAULT '0' COMMENT '召喚数',
  `summon_interval` int NOT NULL DEFAULT '0' COMMENT '召喚間隔',
  `action_delay` int NOT NULL DEFAULT '0' COMMENT 'アクションするまでの時間',
  `summon_position` double NOT NULL DEFAULT '0' COMMENT '召喚位置',
  `move_start_condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '移動を開始する条件',
  `move_start_condition_value` bigint NOT NULL DEFAULT '0' COMMENT '移動開始条件で使用するパラメータ',
  `move_stop_condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '移動を停止する条件',
  `move_stop_condition_value` bigint NOT NULL DEFAULT '0' COMMENT '移動停止条件で使用するパラメータ',
  `move_restart_condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '移動停止後に再度移動開始する条件',
  `move_restart_condition_value` bigint NOT NULL DEFAULT '0' COMMENT '再移動開始条件で使用するパラメータ',
  `move_loop_count` int NOT NULL DEFAULT '0' COMMENT '移動・停止を繰り返す回数',
  `last_boss_trigger` tinyint NOT NULL DEFAULT '0' COMMENT 'ラスボストリガー',
  `aura_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Default' COMMENT 'オーラタイプ',
  `death_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '死亡タイプ',
  `override_drop_battle_point` int DEFAULT NULL COMMENT '上書きするドロップバトルポイント',
  `defeated_score` int NOT NULL DEFAULT '0' COMMENT '撃破スコア',
  `enemy_hp_coef` double NOT NULL DEFAULT '0' COMMENT '敵HP係数',
  `enemy_attack_coef` double NOT NULL DEFAULT '0' COMMENT '敵攻撃力係数',
  `enemy_speed_coef` double NOT NULL DEFAULT '0' COMMENT '敵速度係数',
  `deactivation_condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT 'このSequenceが非活性化する条件',
  `deactivation_condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '非活性化条件で使用するパラメータ',
  `is_summon_unit_outpost_damage_invalidation` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '召喚ユニットの基地ダメージを無効化するか',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲーム内敵出現ルール';

-- Table: mst_battle_point_levels
CREATE TABLE `mst_battle_point_levels` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `level` int NOT NULL COMMENT 'レベル(当初は貯まるとレベルを上げて更にポイントが貯まる速度とかを強化できる仕様だった)',
  `required_level_up_battle_point` int NOT NULL COMMENT 'レベルを上げるために必要なポイント',
  `max_battle_point` int NOT NULL COMMENT '上限ポイント',
  `charge_amount` int NOT NULL COMMENT '一回で貯まるポイントの量',
  `charge_interval` int NOT NULL COMMENT '何フレーム毎にポイントが貯まるかの設定',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='インゲームで使用するバトルポイントについての設定(初期ポイント、ポイントが貯まる速度、最大ポイント etc) (現在未使用)';

-- Table: mst_cheat_settings
CREATE TABLE `mst_cheat_settings` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `content_type` enum('AdventBattle','Pvp') COLLATE utf8mb4_bin NOT NULL COMMENT 'コンテンツのタイプ',
  `cheat_type` enum('BattleTime','MaxDamage','BattleStatusMismatch','MasterDataStatusMismatch') COLLATE utf8mb4_bin NOT NULL COMMENT 'チートタイプ',
  `cheat_value` int NOT NULL COMMENT 'チートとする値',
  `is_excluded_ranking` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'チート検出時に即ランキング除外するか',
  `start_at` timestamp NOT NULL COMMENT 'チート設定開始日',
  `end_at` timestamp NOT NULL COMMENT 'チート設定終了日',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームコンテンツごとに検出すべきチート手法と閾値を設定';

-- Table: mst_comeback_bonus_schedules
CREATE TABLE `mst_comeback_bonus_schedules` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `inactive_condition_days` int unsigned NOT NULL COMMENT '未ログイン期間の条件日数',
  `duration_days` int NOT NULL COMMENT '有効日数',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='カムバックボーナスのスケジュール設定(現在未対応、対応予定あり)';

-- Table: mst_comeback_bonuses
CREATE TABLE `mst_comeback_bonuses` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_comeback_bonus_schedule_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_comeback_bonus_schedules.id',
  `login_day_count` int unsigned NOT NULL COMMENT '条件とするログイン日数',
  `mst_daily_bonus_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_daily_bonus_reward.group_id',
  `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_schedule_id_login_day_count` (`mst_comeback_bonus_schedule_id`,`login_day_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='カムバックボーナスの設定(現在未対応、対応予定あり)';

-- Table: mst_configs
CREATE TABLE `mst_configs` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'キー',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '設定値',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_configs_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='定数設定';

-- Table: mst_daily_bonus_rewards
CREATE TABLE `mst_daily_bonus_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '報酬グルーピングID',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数量',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ログインボーナスの設定用テーブル(ただ、現在は通常ログインボーナスもイベントログインボーナスもmst_mission_rewardsで設定されている)(現在は未使用)';

-- Table: mst_defense_targets
CREATE TABLE `mst_defense_targets` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アセットキー',
  `position` decimal(10,2) NOT NULL COMMENT 'インゲーム内座標',
  `hp` int NOT NULL COMMENT '守備対象HP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲーム向け防衛オブジェクトの設定';

-- Table: mst_dummy_outposts
CREATE TABLE `mst_dummy_outposts` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_dummy_user_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_dummy_users.id',
  `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_outpost_enhancements.id',
  `level` int NOT NULL DEFAULT '1' COMMENT 'アウトポストレベル',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_dummy_user_outpost_enhancement_unique` (`mst_dummy_user_id`,`mst_outpost_enhancement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ダミーユーザーのゲート情報マスターテーブル';

-- Table: mst_dummy_user_artworks
CREATE TABLE `mst_dummy_user_artworks` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_dummy_user_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_dummy_users.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_artwork.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_dummy_user_artworks_unique` (`mst_dummy_user_id`,`mst_artwork_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ダミーユーザーの原画所持マスターテーブル';

-- Table: mst_dummy_user_units
CREATE TABLE `mst_dummy_user_units` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_dummy_user_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_dummy_users.id',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.id',
  `level` int NOT NULL DEFAULT '1' COMMENT 'ユニットレベル',
  `rank` int NOT NULL DEFAULT '0' COMMENT 'ユニットランク',
  `grade_level` int NOT NULL DEFAULT '1' COMMENT 'ユニットグレードレベル',
  PRIMARY KEY (`id`),
  KEY `mst_dummy_user_units_mst_dummy_user_id_index` (`mst_dummy_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ダミーユーザーのユニット情報マスターテーブル';

-- Table: mst_dummy_users
CREATE TABLE `mst_dummy_users` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_units.id',
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_emblems.id',
  `grade_unit_level_total_count` int NOT NULL DEFAULT '1' COMMENT '図鑑効果用グレードレベル合計',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ダミーユーザー情報のマスターテーブル';

-- Table: mst_dummy_users_i18n
CREATE TABLE `mst_dummy_users_i18n` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_dummy_user_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_dummy_users.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ダミーユーザー名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_dummy_users_i18n_mst_dummy_user_id_language_unique` (`mst_dummy_user_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ダミーユーザー情報の多言語対応テーブル';

-- Table: mst_emblems
CREATE TABLE `mst_emblems` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `emblem_type` enum('Event','Series') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'エンブレムのタイプ',
  `mst_series_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作品ID',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報ソース',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='エンブレム設定';

-- Table: mst_emblems_i18n
CREATE TABLE `mst_emblems_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_emblem_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_emblems.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'エンブレムの名称',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'フレーバーテキスト',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_emblem_id_index` (`mst_emblem_id`),
  KEY `language_index` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='エンブレム設定の多言語設定';

-- Table: mst_enemy_characters
CREATE TABLE `mst_enemy_characters` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報ソース',
  `is_phantomized` tinyint NOT NULL DEFAULT '0' COMMENT 'プレイアブルキャラの敵化専用表現用',
  `is_displayed_encyclopedia` tinyint NOT NULL DEFAULT '0' COMMENT '図鑑に表示するかのフラグ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='敵ユニットの設定';

-- Table: mst_enemy_characters_i18n
CREATE TABLE `mst_enemy_characters_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ファントムID',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ファントム名',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ファントム説明',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='敵ユニットの設定の多言語設定';

-- Table: mst_enemy_outposts
CREATE TABLE `mst_enemy_outposts` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `hp` int NOT NULL COMMENT '敵タワーHP',
  `outpost_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '敵タワーアセットキー',
  `artwork_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '敵タワー原画アセットキー',
  `is_damage_invalidation` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '無敵判定',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='敵タワーの設定';

-- Table: mst_enemy_stage_parameters
CREATE TABLE `mst_enemy_stage_parameters` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'リレーション向けMstEnemyCharacterId',
  `character_unit_kind` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal' COMMENT '敵カテゴリ(通常、ボス、降臨バトルボス)',
  `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ロールタイプ',
  `color` enum('None','Colorless','Red','Blue','Yellow','Green') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '影色',
  `sort_order` int NOT NULL COMMENT '表示並び順',
  `hp` int NOT NULL COMMENT 'HP',
  `damage_knock_back_count` int NOT NULL COMMENT '被撃破までのHP減少によるノックバック回数',
  `move_speed` int NOT NULL COMMENT '移動速度',
  `well_distance` double NOT NULL COMMENT '索敵距離',
  `attack_power` int NOT NULL COMMENT '攻撃力',
  `attack_combo_cycle` int NOT NULL COMMENT '攻撃サイクル',
  `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT '' COMMENT 'リレーション向けMstUnitAbilityId',
  `drop_battle_point` int NOT NULL COMMENT '被撃破時のリーダーP獲得量',
  `mst_transformation_enemy_stage_parameter_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstTransformationEnemyStageParameterId',
  `transformation_condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '敵が変身する際の条件',
  `transformation_condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '変身条件に使用するパラメータ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲーム向け敵ユニットの設定';

-- Table: mst_event_bonus_units
CREATE TABLE `mst_event_bonus_units` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ボーナス対象キャラID',
  `bonus_percentage` int NOT NULL COMMENT 'ステータスボーナス割合',
  `event_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ボーナスグループ',
  `is_pick_up` tinyint unsigned NOT NULL COMMENT 'ボーナスキャラ簡易表示の対象フラグ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラごとのイベントボーナス設定';

-- Table: mst_event_display_rewards
CREATE TABLE `mst_event_display_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstEventId',
  `resource_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リソースカテゴリ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けリソースId',
  `sort_order` int NOT NULL COMMENT '表示順番',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='(現在未使用)旧イベントクエスト画面で出す目玉報酬の設定';

-- Table: mst_event_display_units
CREATE TABLE `mst_event_display_units` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstQuestId',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstUnitId',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントTOP画面で表示するユニットデータ';

-- Table: mst_event_display_units_i18n
CREATE TABLE `mst_event_display_units_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_event_display_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstEventDisplayUnitId',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '言語設定',
  `speech_balloon_text1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '吹き出しセリフ1',
  `speech_balloon_text2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '吹き出しセリフ2',
  `speech_balloon_text3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '吹き出しセリフ3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントTOP画面で表示するユニットデータの多言語設定';

-- Table: mst_events
CREATE TABLE `mst_events` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_series_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '作品ID',
  `is_displayed_series_logo` tinyint NOT NULL DEFAULT '0' COMMENT '作品ロゴの表示有無',
  `is_displayed_jump_plus` tinyint NOT NULL DEFAULT '0' COMMENT '作品を読むボタンの表示有無',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'アセットキー',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_series_id_index` (`mst_series_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベント設定';

-- Table: mst_events_i18n
CREATE TABLE `mst_events_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_event_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'リレーション向けMstEventId',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '言語設定',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'イベント名',
  `balloon` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '吹き出し内テキスト',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_event_id_language` (`mst_event_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベント設定の多言語設定';

-- Table: mst_exchange_costs
CREATE TABLE `mst_exchange_costs` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `mst_exchange_lineup_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_exchange_lineups.id',
  `cost_type` enum('Coin','Item') COLLATE utf8mb4_bin NOT NULL COMMENT 'コストタイプ',
  `cost_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'コストID',
  `cost_amount` int unsigned NOT NULL COMMENT '必要数量',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `idx_mst_exchange_lineup_id` (`mst_exchange_lineup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='交換コストマスタ';

-- Table: mst_exchange_lineups
CREATE TABLE `mst_exchange_lineups` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'グループID',
  `tradable_count` int unsigned DEFAULT NULL COMMENT '交換上限数（null=無制限）',
  `display_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順序',
  PRIMARY KEY (`id`),
  KEY `idx_group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='交換ラインナップマスタ';

-- Table: mst_exchange_rewards
CREATE TABLE `mst_exchange_rewards` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `mst_exchange_lineup_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_exchange_lineups.id',
  `resource_type` enum('Coin','FreeDiamond','Item','Emblem','Unit','Artwork') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数量',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `idx_mst_exchange_lineup_id` (`mst_exchange_lineup_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='交換報酬マスタ';

-- Table: mst_exchanges
CREATE TABLE `mst_exchanges` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `mst_event_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_events.id',
  `exchange_trade_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '交換所の種類',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `start_at` timestamp NOT NULL COMMENT '開催開始日時',
  `end_at` timestamp NULL DEFAULT NULL COMMENT '開催終了日時',
  `lineup_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ラインナップグループID',
  `display_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='交換所マスタ';

-- Table: mst_exchanges_i18n
CREATE TABLE `mst_exchanges_i18n` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_exchange_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_exchanges.id',
  `language` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `name` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '交換所名',
  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'アセットキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_exchange_id_language` (`mst_exchange_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='交換所マスタ多言語';

-- Table: mst_fragment_box_groups
CREATE TABLE `mst_fragment_box_groups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_fragment_box_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'BOXのアイテムID',
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ラインナップされるかけらのアイテムID',
  `start_at` timestamp NOT NULL COMMENT 'ラインナップアイテムの開始日',
  `end_at` timestamp NOT NULL COMMENT 'ラインナップアイテムの終了日',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_fragment_box_group_id_index` (`mst_fragment_box_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='かけらボックスアイテムの交換先アイテム設定';

-- Table: mst_fragment_boxes
CREATE TABLE `mst_fragment_boxes` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstItemId',
  `mst_fragment_box_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstFragmentBoxGroupId',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_fragment_boxes_mst_item_id_unique` (`mst_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='かけらボックスアイテム設定';

-- Table: mst_home_banners
CREATE TABLE `mst_home_banners` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `destination` enum('None','Gacha','CreditShop','BasicShop','Event','Web','Pack','Pass','BeginnerMission','AdventBattle','Pvp') COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT 'タップ時の遷移先タイプ',
  `destination_path` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'タップ時の遷移先における情報',
  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '表示するバナーのパス',
  `sort_order` int NOT NULL COMMENT 'ホームで出す表示順番',
  `start_at` timestamp NOT NULL COMMENT '掲載開始日時',
  `end_at` timestamp NOT NULL COMMENT '掲載終了日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ホームのバナー設定';

-- Table: mst_idle_incentive_items
CREATE TABLE `mst_idle_incentive_items` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'グループID',
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_items.id',
  `base_amount` decimal(10,4) NOT NULL COMMENT 'ベース量',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索報酬として配布したいアイテムをまとめて複数設定できるテーブル';

-- Table: mst_idle_incentive_rewards
CREATE TABLE `mst_idle_incentive_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬が変わるステージ進捗の閾値',
  `base_coin_amount` decimal(10,4) NOT NULL COMMENT 'N分ごとのコインの基礎獲得数',
  `base_exp_amount` decimal(10,4) NOT NULL COMMENT 'N分ごとの経験値の基礎獲得数',
  `base_rank_up_material_amount` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT 'リミテッドメモリーのベース獲得量',
  `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_idle_incentive_items.mst_idle_incentive_item_group_id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索報酬の基本設定';

-- Table: mst_idle_incentives
CREATE TABLE `mst_idle_incentives` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `initial_reward_receive_minutes` int unsigned NOT NULL COMMENT '放置後最初に報酬が獲得可能になる放置時間',
  `reward_increase_interval_minutes` int unsigned NOT NULL COMMENT '報酬が増加する時間間隔(分)',
  `max_idle_hours` int unsigned NOT NULL COMMENT '最大放置時間',
  `required_quick_receive_diamond_amount` int unsigned NOT NULL DEFAULT '0' COMMENT 'クイック獲得時に必要なプリズム量',
  `max_daily_diamond_quick_receive_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '１日の一次通貨での最大獲得回数',
  `max_daily_ad_quick_receive_amount` int unsigned NOT NULL COMMENT '１日の広告での最大獲得回数',
  `ad_interval_seconds` int unsigned NOT NULL COMMENT '広告視聴のインターバル',
  `quick_idle_minutes` int unsigned NOT NULL COMMENT 'クイック獲得での実質放置時間',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索の基本設定';

-- Table: mst_in_game_gimmick_objects
CREATE TABLE `mst_in_game_gimmick_objects` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アセットキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='各種インゲームコンテンツの特別ルール設定';

-- Table: mst_in_game_special_rule_unit_statuses
CREATE TABLE `mst_in_game_special_rule_unit_statuses` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'グループID',
  `target_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'InGameSpecialRuleUnitStatusTargetTypeで指定する',
  `target_value` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.idやロール、属性などを指定する',
  `status_parameter_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'InGameSpecialRuleUnitStatusParameterTypeで指定する',
  `effect_value` int NOT NULL COMMENT '効果値',
  PRIMARY KEY (`id`),
  KEY `mst_in_game_special_rule_unit_statuses_group_id_index` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mst_in_game_special_rules
CREATE TABLE `mst_in_game_special_rules` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `content_type` enum('Stage','AdventBattle','Pvp') COLLATE utf8mb4_bin NOT NULL COMMENT 'インゲームコンテンツタイプ',
  `target_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '各インゲームコンテンツごとの対象マスタテーブルのID',
  `rule_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ルール条件タイプ',
  `rule_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ルール条件値',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='アイテムのレアリティ別交換レート設定';

-- Table: mst_in_games
CREATE TABLE `mst_in_games` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_auto_player_sequence_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstAutoPlayerSequenceId',
  `mst_auto_player_sequence_set_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_auto_player_sequences.sequence_set_id',
  `bgm_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'BGMアセットキー',
  `boss_bgm_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ボスBGM',
  `loop_background_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '背景アセットキー',
  `player_outpost_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'プレイヤータワーアセットキー',
  `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstPageId',
  `mst_enemy_outpost_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstEnemyOutpostId',
  `mst_defense_target_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'リレーション向けMstDefenceTargetId',
  `boss_mst_enemy_stage_parameter_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'リレーション向けMstEnemyStageParameterId',
  `boss_count` int DEFAULT NULL COMMENT 'ボスの出現数',
  `normal_enemy_hp_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵HP倍率(通常敵)',
  `normal_enemy_attack_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵攻撃倍率(通常敵)',
  `normal_enemy_speed_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵スピード倍率(通常敵)',
  `boss_enemy_hp_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵HP倍率(ボス)',
  `boss_enemy_attack_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵攻撃倍率(ボス)',
  `boss_enemy_speed_coef` decimal(10,2) NOT NULL COMMENT 'ステージ内敵スピード倍率(ボス)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームの設定';

-- Table: mst_in_games_i18n
CREATE TABLE `mst_in_games_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_in_game_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'リレーション向けMstInGameId',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '言語設定',
  `result_tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '敗北時Tips',
  `description` text COLLATE utf8mb4_bin NOT NULL COMMENT 'ステージ情報',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_language` (`mst_in_game_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームの設定の多言語設定';

-- Table: mst_item_rarity_trades
CREATE TABLE `mst_item_rarity_trades` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `rarity` enum('N','R','SR','SSR','UR') COLLATE utf8mb4_bin NOT NULL COMMENT 'レアリティ',
  `cost_amount` int unsigned NOT NULL DEFAULT '1' COMMENT '交換元アイテムの必要消費数',
  `reset_type` enum('None','Daily','Weekly','Monthly') COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT 'リセット期間',
  `max_tradable_amount` int unsigned DEFAULT NULL COMMENT '交換上限個数。null: 交換上限なし',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_item_rarity_trades_rarity_unique` (`rarity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラのかけらをかけらBOXに交換する際の設定(交換可能個数、レート、交換可能回数上限、交換可能回数リセット設定)';

-- Table: mst_item_transitions
CREATE TABLE `mst_item_transitions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'リレーション向けMstItemId',
  `transition1` enum('None','MainQuest','EventQuest','ShopItem','Pack','Achievement','LoginBonus','DailyMission','WeeklyMission','Patrol','ExchangeShop','Etc') COLLATE utf8mb4_bin NOT NULL,
  `transition1_mst_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '画面遷移先条件向けMasterDataId',
  `transition2` enum('None','MainQuest','EventQuest','ShopItem','Pack','Achievement','LoginBonus','DailyMission','WeeklyMission','Patrol','ExchangeShop','Etc') COLLATE utf8mb4_bin DEFAULT NULL,
  `transition2_mst_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '画面遷移先条件向けMasterDataId',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='アイテム詳細からの遷移先の設定';

-- Table: mst_items
CREATE TABLE `mst_items` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'アイテムの種別を定義',
  `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アプリの表示タブ用',
  `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レア度',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mst_series.id',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '表示順番',
  `start_date` timestamp NOT NULL COMMENT '開始日',
  `end_date` timestamp NOT NULL COMMENT '終了日',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `destination_opr_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '画面遷移先条件向けOprProductId',
  PRIMARY KEY (`id`),
  KEY `mst_items_item_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アイテム設定';

-- Table: mst_items_i18n
CREATE TABLE `mst_items_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アイテムID',
  `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名前',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_items_i18n_mst_item_id_language_index` (`mst_item_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アイテム設定の多言語設定';

-- Table: mst_koma_lines
CREATE TABLE `mst_koma_lines` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstPageId',
  `row` int NOT NULL COMMENT '列番号',
  `height` double(8,2) NOT NULL COMMENT 'コマ列高さ',
  `koma_line_layout_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマレイアウトアセットキー',
  `koma1_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマアセットキー',
  `koma1_width` double(8,2) DEFAULT NULL COMMENT 'コマ幅',
  `koma1_back_ground_offset` double(8,2) NOT NULL COMMENT 'コマ背景オフセット',
  `koma1_effect_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'コマ効果タイプ',
  `koma1_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ1',
  `koma1_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ2',
  `koma1_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果影響対象',
  `koma1_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果影響カラー',
  `koma1_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果影響ロール',
  `koma2_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマアセットキー',
  `koma2_width` double(8,2) DEFAULT NULL COMMENT 'コマ幅',
  `koma2_back_ground_offset` double(8,2) DEFAULT NULL COMMENT 'コマ背景オフセット',
  `koma2_effect_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'コマ効果タイプ',
  `koma2_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ1',
  `koma2_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ2',
  `koma2_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響対象',
  `koma2_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響カラー',
  `koma2_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響ロール',
  `koma3_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマアセットキー',
  `koma3_width` double(8,2) DEFAULT NULL COMMENT 'コマ幅',
  `koma3_back_ground_offset` double(8,2) DEFAULT NULL COMMENT 'コマ背景オフセット',
  `koma3_effect_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'コマ効果タイプ',
  `koma3_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ1',
  `koma3_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ2',
  `koma3_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響対象',
  `koma3_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響カラー',
  `koma3_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響ロール',
  `koma4_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマアセットキー',
  `koma4_width` double(8,2) DEFAULT NULL COMMENT 'コマ幅',
  `koma4_back_ground_offset` double(8,2) DEFAULT NULL COMMENT 'コマ背景オフセット',
  `koma4_effect_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'コマ効果タイプ',
  `koma4_effect_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ1',
  `koma4_effect_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コマ効果パラメータ2',
  `koma4_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響対象',
  `koma4_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響カラー',
  `koma4_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'コマ効果影響ロール',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='インゲーム内のコマ設定';

-- Table: mst_manga_animations
CREATE TABLE `mst_manga_animations` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
  `condition_type` enum('None','Start','Victory','EnemySummon','EnemyMoveStart','TransformationReady','TransformationStart','TransformationEnd') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '条件タイプ',
  `condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '条件値',
  `animation_start_delay` int NOT NULL COMMENT 'アニメーション開始までの時間',
  `animation_speed` decimal(3,2) NOT NULL DEFAULT '1.00' COMMENT 'アニメーションのスピード',
  `is_pause` tinyint(1) NOT NULL COMMENT '一時停止するか',
  `can_skip` tinyint(1) NOT NULL COMMENT 'スキップ可能か',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'アセットキー',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ステージ原画演出設定';

-- Table: mst_mission_achievement_dependencies
CREATE TABLE `mst_mission_achievement_dependencies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '依存関係のグルーピングID',
  `mst_mission_achievement_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
  `unlock_order` int unsigned NOT NULL COMMENT '対象グループ内でのミッションの開放順。1つ前のunlock_orderを持つミッションをクリアしたら開放される。',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_group_id_mst_mission_achievement_id` (`group_id`,`mst_mission_achievement_id`),
  UNIQUE KEY `uk_group_id_unlock_order` (`group_id`,`unlock_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アチーブメントミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。';

-- Table: mst_mission_achievements
CREATE TABLE `mst_mission_achievements` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `criterion_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
  `unlock_criterion_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '開放条件タイプ',
  `unlock_criterion_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '開放条件値',
  `unlock_criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '開放条件の達成回数',
  `group_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー。mission_full_completeのカウント対象となる。',
  `mst_mission_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_rewards.group_id',
  `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '並び順',
  `destination_scene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アチーブメントミッションの設定';

-- Table: mst_mission_achievements_i18n
CREATE TABLE `mst_mission_achievements_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_mission_achievement_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アチーブメントミッションの設定の多言語設定';

-- Table: mst_mission_beginner_prompt_phrases_i18n
CREATE TABLE `mst_mission_beginner_prompt_phrases_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '言語',
  `prompt_phrase_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '設定文言',
  `start_at` timestamp NOT NULL COMMENT '設定文言を表示する開始期間',
  `end_at` timestamp NOT NULL COMMENT '設定文言を表示する終了期間',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='初心者ミッションのUIの煽り文言の設定';

-- Table: mst_mission_beginners
CREATE TABLE `mst_mission_beginners` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `criterion_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
  `unlock_day` smallint unsigned NOT NULL DEFAULT '0' COMMENT '開始からの開放日',
  `group_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
  `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
  `mst_mission_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='初心者ミッションの設定';

-- Table: mst_mission_beginners_i18n
CREATE TABLE `mst_mission_beginners_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_mission_beginner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_beginners.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ダイアログタイトル',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '初心者ミッションテキスト',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='初心者ミッションの設定の多言語設定';

-- Table: mst_mission_dailies
CREATE TABLE `mst_mission_dailies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `criterion_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
  `group_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
  `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
  `mst_mission_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デイリーミッションの設定';

-- Table: mst_mission_dailies_i18n
CREATE TABLE `mst_mission_dailies_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_mission_daily_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_dailies.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デイリーミッションの設定の多言語設定';

-- Table: mst_mission_daily_bonuses
CREATE TABLE `mst_mission_daily_bonuses` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mission_daily_bonus_type` enum('DailyBonus') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'デイリーボーナスタイプ',
  `login_day_count` int unsigned NOT NULL COMMENT '条件とするログイン日数',
  `mst_mission_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.id',
  `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_type_login_day_count` (`mission_daily_bonus_type`,`login_day_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デイリーボーナス(ログボ)の設定';

-- Table: mst_mission_event_dailies
CREATE TABLE `mst_mission_event_dailies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'イベントID',
  `criterion_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL COMMENT '達成回数',
  `group_key` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '分類キー',
  `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントデイリーミッションの設定';

-- Table: mst_mission_event_dailies_i18n
CREATE TABLE `mst_mission_event_dailies_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_mission_event_daily_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_events.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '説明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントデイリーミッションの設定の多言語設定';

-- Table: mst_mission_event_daily_bonus_schedules
CREATE TABLE `mst_mission_event_daily_bonus_schedules` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_events.id',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `index_mst_event_id` (`mst_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントデイリーボーナス(イベントログボ)のスケジュール設定';

-- Table: mst_mission_event_daily_bonuses
CREATE TABLE `mst_mission_event_daily_bonuses` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_mission_event_daily_bonus_schedule_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_event_daily_bonus_schedules.id',
  `login_day_count` int unsigned NOT NULL COMMENT '条件とするログイン日数',
  `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_reward_groups.id',
  `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_schedule_id_login_day_count` (`mst_mission_event_daily_bonus_schedule_id`,`login_day_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントデイリーボーナス(イベントログボ)の設定';

-- Table: mst_mission_event_dependencies
CREATE TABLE `mst_mission_event_dependencies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '依存関係のグルーピングID',
  `mst_mission_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_events.id',
  `unlock_order` int unsigned NOT NULL COMMENT 'グループ内でのミッションの開放順',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_mst_mission_event_id_unique` (`group_id`,`mst_mission_event_id`),
  UNIQUE KEY `group_id_unlock_order_unique` (`group_id`,`unlock_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。';

-- Table: mst_mission_events
CREATE TABLE `mst_mission_events` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'イベントID',
  `criterion_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL COMMENT '達成回数',
  `unlock_criterion_type` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '開放条件タイプ',
  `unlock_criterion_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '開放条件値',
  `unlock_criterion_count` bigint unsigned NOT NULL COMMENT '達成回数',
  `group_key` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '分類キー',
  `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `event_category` enum('AdventBattle') COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'イベントカテゴリー',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントミッションの設定';

-- Table: mst_mission_events_i18n
CREATE TABLE `mst_mission_events_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_mission_event_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_events.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '説明',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントミッションの設定の多言語設定';

-- Table: mst_mission_limited_term_dependencies
CREATE TABLE `mst_mission_limited_term_dependencies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '依存関係のグルーピングID',
  `mst_mission_limited_term_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_limited_terms.id',
  `unlock_order` int unsigned NOT NULL COMMENT 'グループ内でのミッションの開放順',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_mst_mission_limited_term_id_unique` (`group_id`,`mst_mission_limited_term_id`),
  UNIQUE KEY `group_id_unlock_order_unique` (`group_id`,`unlock_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='期間限定ミッション同士のつながりの設定。あるミッションの開放条件として他ミッション達成を設定したい場合に設定する。';

-- Table: mst_mission_limited_terms
CREATE TABLE `mst_mission_limited_terms` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `progress_group_key` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '進捗グループ',
  `criterion_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL COMMENT '達成回数',
  `mission_category` enum('AdventBattle') COLLATE utf8mb4_bin NOT NULL COMMENT 'ミッションカテゴリー',
  `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ミッションから遷移する画面',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='期間限定ミッションの設定';

-- Table: mst_mission_limited_terms_i18n
CREATE TABLE `mst_mission_limited_terms_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_mission_limited_term_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_mission_limited_terms.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '説明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_mission_limited_term_id_language_unique` (`mst_mission_limited_term_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='期間限定ミッションの設定の多言語設定';

-- Table: mst_mission_rewards
CREATE TABLE `mst_mission_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬グルーピングID',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬リソースID',
  `resource_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '報酬の個数',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ミッション報酬設定';

-- Table: mst_mission_weeklies
CREATE TABLE `mst_mission_weeklies` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `criterion_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
  `criterion_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
  `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
  `group_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
  `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
  `mst_mission_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
  `sort_order` int unsigned NOT NULL COMMENT '並び順',
  `destination_scene` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ウィークリーミッションの設定';

-- Table: mst_mission_weeklies_i18n
CREATE TABLE `mst_mission_weeklies_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_mission_weekly_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_weeklies.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ウィークリーミッションの設定の多言語設定';

-- Table: mst_ng_words
CREATE TABLE `mst_ng_words` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'NGワード',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー名やユニット名でのNGワードリスト設定';

-- Table: mst_outpost_enhancement_levels
CREATE TABLE `mst_outpost_enhancement_levels` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_outpost_enhancement_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '拠点強化ID',
  `level` int unsigned NOT NULL COMMENT 'レベル',
  `cost_coin` int unsigned NOT NULL COMMENT '消費コイン',
  `enhancement_value` double(8,2) NOT NULL COMMENT '強化値',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化項目ごとのレベル設定';

-- Table: mst_outpost_enhancement_levels_i18n
CREATE TABLE `mst_outpost_enhancement_levels_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_outpost_enhancement_level_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '拠点強化レベルID',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '強化時の説明文',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化項目ごとのレベル設定の多言語設定';

-- Table: mst_outpost_enhancements
CREATE TABLE `mst_outpost_enhancements` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_outpost_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '拠点ID',
  `outpost_enhancement_type` enum('LeaderPointSpeed','LeaderPointLimit','OutpostHp','SummonInterval','LeaderPointUp','RushChargeSpeed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '強化タイプ',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化項目の設定';

-- Table: mst_outpost_enhancements_i18n
CREATE TABLE `mst_outpost_enhancements_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_outpost_enhancement_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_outpost_enhancements.id',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '強化できる項目の名前',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化項目の設定の多言語設定';

-- Table: mst_outposts
CREATE TABLE `mst_outposts` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲートの基本設定';

-- Table: mst_pack_contents
CREATE TABLE `mst_pack_contents` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_pack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
  `resource_type` enum('FreeDiamond','Coin','Item','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '内包物のタイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '内包物のID(resource_typeがitemの場合のみ)',
  `resource_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '内包物の数量',
  `is_bonus` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'おまけフラグ',
  `display_order` int unsigned NOT NULL COMMENT '表示順序',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップのパックの内容物の設定';

-- Table: mst_packs
CREATE TABLE `mst_packs` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_products.id',
  `discount_rate` smallint unsigned NOT NULL COMMENT '割引率',
  `pack_type` enum('Daily','Normal') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パック販売タイプ',
  `sale_condition` enum('StageClear','UserLevel') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '販売開始条件',
  `sale_condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '販売開始条件値',
  `sale_hours` smallint unsigned DEFAULT NULL COMMENT '条件達成からの販売時間',
  `tradable_count` int unsigned DEFAULT NULL COMMENT '交換可能個数',
  `cost_type` enum('Cash','Diamond','PaidDiamond','Ad','Free') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '販売コスト種別',
  `cost_amount` int unsigned NOT NULL COMMENT 'コスト量',
  `is_recommend` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'おすすめフラグ',
  `is_first_time_free` tinyint NOT NULL DEFAULT '0' COMMENT '初回無料フラグ',
  `is_display_expiration` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '表示期限があるかどうか',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'バナー画像パス',
  `pack_decoration` enum('Gold') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'パックの装飾',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_packs_sale_condition_index` (`sale_condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップのパック設定';

-- Table: mst_packs_i18n
CREATE TABLE `mst_packs_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_pack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パック名',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップのパック設定の多言語設定';

-- Table: mst_pages
CREATE TABLE `mst_pages` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='インゲーム内のステージ設定';

-- Table: mst_party_unit_counts
CREATE TABLE `mst_party_unit_counts` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id。進捗しているステージ',
  `max_count` int unsigned NOT NULL COMMENT '設定可能なパーティキャラ数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_party_unit_counts_mst_stage_id_unique` (`mst_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='メインクエストの進捗に応じたパーティ編成可能なキャラ数の設定';

-- Table: mst_pvp_bonus_points
CREATE TABLE `mst_pvp_bonus_points` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `condition_value` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'しきい値',
  `bonus_point` int unsigned NOT NULL DEFAULT '0' COMMENT 'ボーナスポイント',
  `bonus_type` enum('ClearTime','WinUpperBonus','WinSameBonus','WinLowerBonus') COLLATE utf8mb4_bin NOT NULL COMMENT 'PVPボーナスタイプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_pvp_bonus_points_threshold_bonus_type_unique` (`condition_value`,`bonus_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVPボーナスポイントのマスターテーブル';

-- Table: mst_pvp_dummies
CREATE TABLE `mst_pvp_dummies` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `rank_class_type` enum('Bronze','Silver','Gold','Platinum') COLLATE utf8mb4_bin NOT NULL,
  `rank_class_level` int unsigned NOT NULL DEFAULT '1',
  `matching_type` enum('Upper','Same','Lower') COLLATE utf8mb4_bin NOT NULL,
  `mst_dummy_user_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_dummy_users.id',
  PRIMARY KEY (`id`),
  KEY `mst_pvp_dummies_rank_class_type_rank_class_level_index` (`rank_class_type`,`rank_class_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVPダミーユーザーのマスターテーブル';

-- Table: mst_pvp_matching_score_ranges
CREATE TABLE `mst_pvp_matching_score_ranges` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `rank_class_type` enum('Bronze','Silver','Gold','Platinum') COLLATE utf8mb4_bin NOT NULL COMMENT 'クラスランク',
  `rank_class_level` int NOT NULL COMMENT 'クラスランクレベル',
  `upper_rank_max_score` int NOT NULL COMMENT '格上スコア足し込み上限',
  `upper_rank_min_score` int NOT NULL COMMENT '格上スコア足し込み下限',
  `same_rank_max_score` int NOT NULL COMMENT '同格スコア足し込み上限',
  `same_rank_min_score` int NOT NULL COMMENT '同格スコア足し込み下限',
  `lower_rank_max_score` int NOT NULL COMMENT '格下スコア足し込み上限',
  `lower_rank_min_score` int NOT NULL COMMENT '格下スコア足し込み下限',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='マッチングに使用するスコアの幅設定情報のマスターテーブル';

-- Table: mst_pvp_ranks
CREATE TABLE `mst_pvp_ranks` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `rank_class_type` enum('Bronze','Silver','Gold','Platinum') COLLATE utf8mb4_bin NOT NULL COMMENT 'PVPランク区分',
  `rank_class_level` int unsigned NOT NULL DEFAULT '1' COMMENT 'PVPランクの最小値',
  `required_lower_score` bigint NOT NULL DEFAULT '1' COMMENT 'PVPランクの最小スコア',
  `win_add_point` int unsigned NOT NULL DEFAULT '0' COMMENT '勝利時のスコア加算値',
  `lose_sub_point` int unsigned NOT NULL DEFAULT '0' COMMENT '敗北時のスコア減算値',
  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ランクアイコンアセットId',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_pvp_ranks_unique` (`rank_class_type`,`rank_class_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVPランクのマスターテーブル';

-- Table: mst_pvp_reward_groups
CREATE TABLE `mst_pvp_reward_groups` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `reward_category` enum('Ranking','RankClass','TotalScore') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'PVP報酬カテゴリ',
  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '報酬条件値',
  `mst_pvp_id` varchar(16) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_pvps.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_pvp_reward_groups_unique` (`mst_pvp_id`,`reward_category`,`condition_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVP報酬グループのマスターテーブル';

-- Table: mst_pvp_rewards
CREATE TABLE `mst_pvp_rewards` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_pvp_reward_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_pvp_reward_groups.id',
  `resource_type` enum('Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '報酬数',
  PRIMARY KEY (`id`),
  KEY `mst_pvp_rewards_mst_pvp_reward_group_id_index` (`mst_pvp_reward_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVP報酬のマスターテーブル';

-- Table: mst_pvps
CREATE TABLE `mst_pvps` (
  `id` varchar(16) COLLATE utf8mb4_bin NOT NULL COMMENT '西暦4桁と週番号2桁を使った自動採番IDを使用',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `ranking_min_pvp_rank_class` enum('Bronze','Silver','Gold','Platinum') COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ランキングに含む最小PVPランク区分',
  `max_daily_challenge_count` int unsigned NOT NULL DEFAULT '0' COMMENT '1日のアイテム消費なし挑戦可能回数',
  `max_daily_item_challenge_count` int unsigned NOT NULL DEFAULT '0' COMMENT '1日のアイテム消費あり挑戦可能回数',
  `item_challenge_cost_amount` int unsigned NOT NULL DEFAULT '0' COMMENT 'アイテム消費あり挑戦時の消費アイテム数',
  `initial_battle_point` int NOT NULL COMMENT '初期バトルポイント',
  `mst_in_game_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_in_games.id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVP情報のマスターテーブル';

-- Table: mst_pvps_i18n
CREATE TABLE `mst_pvps_i18n` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_pvp_id` varchar(16) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_pvps.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `name` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'PVP名',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'PVP説明',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_pvps_i18n_unique` (`mst_pvp_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='PVP情報の多言語対応テーブル';

-- Table: mst_quest_bonus_units
CREATE TABLE `mst_quest_bonus_units` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_quest_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_quests.id',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.id',
  `coin_bonus_rate` double NOT NULL COMMENT 'コイン報酬量の上昇倍率',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  PRIMARY KEY (`id`),
  KEY `idx_mst_quest_id_mst_unit_id` (`mst_quest_id`,`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラごとのコインクエストボーナス設定';

-- Table: mst_quest_event_bonus_schedules
CREATE TABLE `mst_quest_event_bonus_schedules` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ボーナス対象クエストID',
  `event_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_stage_event_bonus_unitsのevent_bonus_group_id',
  `start_at` timestamp NOT NULL COMMENT '開始日',
  `end_at` timestamp NOT NULL COMMENT '終了日',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='クエストごとのイベントボーナス設定';

-- Table: mst_quests
CREATE TABLE `mst_quests` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `quest_type` enum('Normal','Event','Enhance','Tutorial') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストの種類',
  `mst_event_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_events.id',
  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mst_series.id',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'ソート順序',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `start_date` timestamp NOT NULL COMMENT '開始日',
  `end_date` timestamp NOT NULL COMMENT '終了日',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `quest_group` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '同クエストとして表示をまとめるグループ',
  `difficulty` enum('Normal','Hard','Extra') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal' COMMENT '難易度',
  PRIMARY KEY (`id`),
  KEY `idx_mst_event_id` (`mst_event_id`),
  KEY `idx_quest_type` (`quest_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='クエスト設定';

-- Table: mst_quests_i18n
CREATE TABLE `mst_quests_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_quests.id',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名前',
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'カテゴリ名',
  `flavor_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'フレーバーテキスト',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='クエスト名などの多言語設定';

-- Table: mst_release_keys
CREATE TABLE `mst_release_keys` (
  `release_key` int NOT NULL COMMENT 'リリースキー',
  `start_at` datetime NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '開始日時',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '説明文',
  PRIMARY KEY (`release_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='リリースキー設定';

-- Table: mst_result_tips_i18n
CREATE TABLE `mst_result_tips_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `user_level` int unsigned NOT NULL COMMENT 'レベル',
  `result_tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Tips本文',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='敗北時の汎用Tipsテキストの多言語設定';

-- Table: mst_series
CREATE TABLE `mst_series` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作品ID',
  `jump_plus_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ジャンプ+作品へのURL',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アセットキー',
  `banner_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'バナーのアセット',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ジャンプ+の漫画作品をGLOW内で識別するための情報の設定';

-- Table: mst_series_i18n
CREATE TABLE `mst_series_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_series_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作品名',
  `prefix_word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '絞込も文字(ア行など)',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_series_id_language` (`mst_series_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='作品の言語設定';

-- Table: mst_shop_items
CREATE TABLE `mst_shop_items` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `shop_type` enum('Coin','Daily','Weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品タイプ',
  `cost_type` enum('Coin','Diamond','PaidDiamond','Ad','Free') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '消費するコストのタイプ',
  `cost_amount` int unsigned DEFAULT '0' COMMENT '消費するコストの数量',
  `is_first_time_free` tinyint(1) NOT NULL COMMENT '初回無料か',
  `tradable_count` int unsigned DEFAULT NULL COMMENT '交換可能回数',
  `resource_type` enum('FreeDiamond','Coin','IdleCoin','Item') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '獲得物のタイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '獲得物のID',
  `resource_amount` bigint unsigned NOT NULL COMMENT '獲得物の数量',
  `start_date` timestamp NOT NULL COMMENT '販売開始日時',
  `end_date` timestamp NOT NULL COMMENT '販売終了日時',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーに販売する非課金商品を管理する';

-- Table: mst_shop_pass_effects
CREATE TABLE `mst_shop_pass_effects` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_shop_pass_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_shop_passes.id',
  `effect_type` enum('IdleIncentiveAddReward','IdleIncentiveMaxQuickReceiveByDiamond','IdleIncentiveMaxQuickReceiveByAd','StaminaAddRecoveryLimit','AdSkip','ChangeBattleSpeed') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ゲーム内パス効果',
  `effect_value` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ゲーム内パス効果設定値',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `idx_mst_shop_pass_id` (`mst_shop_pass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ショップパスの効果設定';

-- Table: mst_shop_pass_rewards
CREATE TABLE `mst_shop_pass_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_shop_pass_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_shop_passes.id',
  `pass_reward_type` enum('Daily','Immediately') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬の受け取りタイミング',
  `resource_type` enum('Coin','FreeDiamond','Item') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬リソースID',
  `resource_amount` bigint unsigned NOT NULL COMMENT '報酬の個数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `idx_mst_shop_pass_id` (`mst_shop_pass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ショップパスの報酬設定';

-- Table: mst_shop_passes
CREATE TABLE `mst_shop_passes` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `opr_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_products.id',
  `is_display_expiration` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '販売の有効期限を表示するかどうか 0:表示しない 1:表示する',
  `pass_duration_days` int unsigned NOT NULL COMMENT 'パスの有効日数',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'アセットキー',
  `shop_pass_cell_color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'パス表示バナーの背景色',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_opr_product_id` (`opr_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ショップパスの基本設定';

-- Table: mst_shop_passes_i18n
CREATE TABLE `mst_shop_passes_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_shop_pass_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_shop_passes.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'パス名',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_shop_pass_id_language` (`mst_shop_pass_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ショップパスの基本設定の多言語設定';

-- Table: mst_special_attacks_i18n
CREATE TABLE `mst_special_attacks_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_units.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名前',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='必殺ワザの名前設定';

-- Table: mst_special_role_level_up_attack_elements
CREATE TABLE `mst_special_role_level_up_attack_elements` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_attack_element_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '必殺ワザID',
  `min_power_parameter` decimal(10,2) NOT NULL COMMENT 'スペシャルロールユニットにおけるレベル最小時の攻撃パラメータ',
  `max_power_parameter` decimal(10,2) NOT NULL COMMENT 'スペシャルロールユニットにおけるレベル最大時の攻撃パラメータ',
  `min_effective_count` int NOT NULL COMMENT '強化するeffective_countの最低値',
  `max_effective_count` int NOT NULL COMMENT '強化するeffective_countの最高値',
  `min_effective_duration` int NOT NULL COMMENT '強化するeffective_durationの最低値',
  `max_effective_duration` int NOT NULL COMMENT '強化するeffective_durationの最高値',
  `min_effect_parameter` decimal(10,2) NOT NULL COMMENT '強化するeffect_parameterの最低値',
  `max_effect_parameter` decimal(10,2) NOT NULL COMMENT '強化するeffect_parameterの最高値',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='スペシャルキャラのレベルアップに応じて';

-- Table: mst_speech_balloons_i18n
CREATE TABLE `mst_speech_balloons_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstUnitId',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `condition_type` enum('Summon','SpecialAttackCharge','SpecialAttack') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件タイプ',
  `balloon_type` enum('Maru','Fuwa','Toge') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '吹き出しタイプ',
  `side` enum('Right','Left') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '向きタイプ',
  `duration` double NOT NULL COMMENT '間隔',
  `text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'セリフ',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット吹き出しテキストの多言語設定';

-- Table: mst_stage_clear_time_rewards
CREATE TABLE `mst_stage_clear_time_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
  `upper_clear_time_ms` int unsigned NOT NULL COMMENT '目標タイム(ミリ秒)',
  `resource_type` enum('Coin','FreeDiamond','Item','Emblem','Unit') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ステージごとのクリアタイムに応じた報酬設定';

-- Table: mst_stage_end_conditions
CREATE TABLE `mst_stage_end_conditions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
  `stage_end_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Victory' COMMENT 'Victory(勝利)・Defeat(敗北)・Finish(終了)の3タイプのどれに当たるかを設定',
  `condition_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'PlayerOutpostBreakDown' COMMENT '条件を設定',
  `condition_value1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '条件が敵討伐であれば討伐数など、条件によって必要な数など値を指定する(不要な場合は空)',
  `condition_value2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '条件が敵討伐であれば討伐数など、条件によって必要な数など値を指定する(複数必要な場合)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームにて、特殊な勝利・敗北などのバトル終了条件を設定したい時に';

-- Table: mst_stage_enhance_reward_params
CREATE TABLE `mst_stage_enhance_reward_params` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `min_threshold_score` bigint unsigned NOT NULL COMMENT '乗数が適用されるスコアの下限値',
  `coin_reward_amount` bigint unsigned NOT NULL COMMENT '報酬量',
  `coin_reward_size_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '報酬のサイズタイプ',
  `release_key` bigint unsigned NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_min_threshold_score` (`min_threshold_score`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='強化クエストステージクリア時の報酬量算出に使う係数設定';

-- Table: mst_stage_event_rewards
CREATE TABLE `mst_stage_event_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
  `reward_category` enum('Always','FirstClear','Random') COLLATE utf8mb4_bin NOT NULL COMMENT '報酬カテゴリー',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数',
  `percentage` int unsigned NOT NULL COMMENT 'ドロップの確率(パーセント)',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='イベントクエストステージのクリア報酬設定';

-- Table: mst_stage_event_settings
CREATE TABLE `mst_stage_event_settings` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
  `reset_type` enum('Daily') COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'リセットタイプ',
  `clearable_count` int DEFAULT NULL COMMENT 'クリア可能回数',
  `ad_challenge_count` int NOT NULL DEFAULT '0' COMMENT '広告視聴で挑戦できる回数',
  `background_asset_key` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '背景',
  `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_stage_id` (`mst_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ステージの基本設定にはないイベントクエスト用の追加設定';

-- Table: mst_stage_rewards
CREATE TABLE `mst_stage_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `reward_category` enum('Always','FirstClear','Random') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬カテゴリー',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int unsigned NOT NULL COMMENT '報酬数',
  `percentage` int unsigned NOT NULL COMMENT '出現比重',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
  `release_key` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_stage_id_index` (`mst_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージ報酬設定';

-- Table: mst_stage_tips
CREATE TABLE `mst_stage_tips` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語設定',
  `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstStageTipsGroupId',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'タイトル',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '本文',
  `release_key` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージで表示するtips設定';

-- Table: mst_stages
CREATE TABLE `mst_stages` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クエストID(mst_quest.id)',
  `mst_in_game_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'インゲーム設定ID(mst_in_game.id)',
  `stage_number` int NOT NULL DEFAULT '0' COMMENT 'ステージ番号',
  `recommended_level` int NOT NULL DEFAULT '1' COMMENT 'おすすめレベル',
  `cost_stamina` int unsigned NOT NULL COMMENT '消費スタミナ',
  `exp` int unsigned NOT NULL COMMENT '獲得EXP',
  `coin` int unsigned NOT NULL COMMENT '獲得コイン',
  `mst_artwork_fragment_drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
  `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '解放条件のステージID',
  `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'tipsID',
  `auto_lap_type` enum('AfterClear','Initial') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'スタミナブーストタイプ',
  `max_auto_lap_count` int unsigned NOT NULL DEFAULT '1' COMMENT '最大スタミナブースト周回指定可能数',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
  `start_at` timestamp NOT NULL COMMENT 'ステージ公開開始日時',
  `end_at` timestamp NOT NULL COMMENT 'ステージ公開終了日時',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'アセットキー',
  `release_key` bigint unsigned NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージの基本設定';

-- Table: mst_stages_i18n
CREATE TABLE `mst_stages_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ステージID(mst_stage.id)',
  `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ステージ名',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージの基本設定の多言語設定';

-- Table: mst_store_products
CREATE TABLE `mst_store_products` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL COMMENT '変更が適用されるリリースキー',
  `product_id_ios` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStoreのプロダクトID',
  `product_id_android` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'GooglePlayのプロダクトID',
  PRIMARY KEY (`id`),
  KEY `product_id_ios_index` (`product_id_ios`),
  KEY `product_id_android_index` (`product_id_android`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='プラットフォーム（AppStore, GooglePlay）に登録している商品のIDを管理する\nリストアがあるため一度定義したら変えない';

-- Table: mst_store_products_i18n
CREATE TABLE `mst_store_products_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_products.id',
  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語設定',
  `price_ios` decimal(10,3) NOT NULL COMMENT 'AppStoreの価格',
  `price_android` decimal(10,3) NOT NULL COMMENT 'GooglePlayの価格',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ストア商品情報の多言語を管理する';

-- Table: mst_tutorial_tips_i18n
CREATE TABLE `mst_tutorial_tips_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_tutorial_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_tutorials.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '並び順(昇順)',
  `title` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'タイトル',
  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アセットキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='チュートリアルダイアログで表示するtips画像パスの設定';

-- Table: mst_tutorials
CREATE TABLE `mst_tutorials` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `type` enum('Intro','Main','Free') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Intro' COMMENT 'チュートリアルタイプ',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT '各チュートリアルコンテンツの順番',
  `function_name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'チュートリアル名',
  `condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'フリーパートの開放条件種別',
  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'フリーパートの開放条件値',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_tutorials_function_name_unique` (`function_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='チュートリアルの項目と開始条件の設定';

-- Table: mst_unit_abilities
CREATE TABLE `mst_unit_abilities` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_ability_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstAbilityId',
  `ability_parameter1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Abilityごとに使用するパラメータ1',
  `ability_parameter2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Abilityごとに使用するパラメータ2',
  `ability_parameter3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Abilityごとに使用するパラメータ3',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット特性の設定';

-- Table: mst_unit_encyclopedia_effects
CREATE TABLE `mst_unit_encyclopedia_effects` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `mst_unit_encyclopedia_reward_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_unit_encyclopedia_rewards.id',
  `effect_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '効果種別',
  `value` double NOT NULL COMMENT '効果値',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラ図鑑報酬のインゲーム効果設定';

-- Table: mst_unit_encyclopedia_rewards
CREATE TABLE `mst_unit_encyclopedia_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `unit_encyclopedia_rank` int unsigned NOT NULL COMMENT '図鑑ランク(グレードの合算値)',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬リソースID',
  `resource_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '報酬の個数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラ図鑑報酬の設定';

-- Table: mst_unit_fragment_converts
CREATE TABLE `mst_unit_fragment_converts` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ラベル',
  `convert_amount` int unsigned NOT NULL COMMENT 'ガチャとかでキャラが重複した際に変換されるキャラのかけら数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_unit_fragment_converts_rarity_unique` (`unit_label`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラ重複獲得時のキャラのかけらへの変換レートの設定';

-- Table: mst_unit_grade_coefficients
CREATE TABLE `mst_unit_grade_coefficients` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ラベル',
  `grade_level` int unsigned NOT NULL COMMENT 'グレードレベル',
  `coefficient` int unsigned NOT NULL COMMENT '体力と攻撃力に係る係数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラのグレードアップ後のステータス係数の設定';

-- Table: mst_unit_grade_ups
CREATE TABLE `mst_unit_grade_ups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ラベル',
  `grade_level` int unsigned NOT NULL COMMENT 'グレードレベル',
  `require_amount` int unsigned NOT NULL COMMENT 'グレードアップに必要なかけら数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_label_grade_level` (`unit_label`,`grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラのグレードアップ設定';

-- Table: mst_unit_level_ups
CREATE TABLE `mst_unit_level_ups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユニットタイプ',
  `level` int NOT NULL COMMENT 'レベル',
  `required_coin` int NOT NULL COMMENT 'レベルアップに必要なコイン',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_label_level` (`unit_label`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラのレベルアップ設定';

-- Table: mst_unit_rank_coefficients
CREATE TABLE `mst_unit_rank_coefficients` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `rank` int unsigned NOT NULL COMMENT 'ユニットのランク',
  `coefficient` int NOT NULL COMMENT '係数',
  `special_unit_coefficient` int NOT NULL COMMENT 'スペシャルキャラ用のランクステータス上昇率',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャラのランクアップ後のステータス係数の設定';

-- Table: mst_unit_rank_ups
CREATE TABLE `mst_unit_rank_ups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユニットラベル',
  `rank` int NOT NULL COMMENT 'Lv上限開放後のユニットのランク',
  `amount` int NOT NULL COMMENT 'リミテッドメモリーの必要数',
  `require_level` int NOT NULL COMMENT 'Lv上限開放に必要なユニットのレベル',
  `sr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '初級メモリーフラグメントの必要数',
  `ssr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '中級メモリーフラグメントの必要数',
  `ur_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '上級メモリーフラグメントの必要数',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_unit_label_rank` (`unit_label`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラのランクアップ設定';

-- Table: mst_unit_role_bonuses
CREATE TABLE `mst_unit_role_bonuses` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '該当のロールタイプ',
  `color_advantage_attack_bonus` decimal(10,2) NOT NULL COMMENT '該当ロールが攻撃時に属性有利だった時にダメージ計算にかけるボーナス係数(1.0が基準値。1.3などでダメージが大きくなる)',
  `color_advantage_defense_bonus` decimal(10,2) NOT NULL COMMENT '該当ロールが防御時に属性有利だった時にダメージ計算にかけるボーナス係数(1.0が基準値。0.8などでダメージが小さくなる)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームでのロールごとの属性有利時の攻撃と防御にかけるボーナス係数の設定';

-- Table: mst_unit_specific_rank_ups
CREATE TABLE `mst_unit_specific_rank_ups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_units.id',
  `rank` int NOT NULL COMMENT 'Lv上限開放後のユニットのランク',
  `amount` int NOT NULL COMMENT 'リミテッドメモリーの必要数',
  `unit_memory_amount` int unsigned NOT NULL DEFAULT '0' COMMENT 'キャラ個別メモリーの必要数',
  `require_level` int NOT NULL COMMENT 'Lv上限開放に必要なユニットのレベル',
  `sr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '初級メモリーフラグメントの必要数',
  `ssr_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '中級メモリーフラグメントの必要数',
  `ur_memory_fragment_amount` int NOT NULL DEFAULT '0' COMMENT '上級メモリーフラグメントの必要数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_unit_id_rank` (`mst_unit_id`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ユニット個別のランクアップ設定';

-- Table: mst_units
CREATE TABLE `mst_units` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'かけらID(mst_items.id)',
  `color` enum('Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colorless' COMMENT '属性',
  `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '属性',
  `attack_range_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ロール',
  `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ラベル',
  `has_specific_rank_up` tinyint NOT NULL DEFAULT '0' COMMENT 'キャラ個別のランクアップ設定を使うかどうか',
  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'アセットキー',
  `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レアリティ',
  `sort_order` int unsigned NOT NULL COMMENT 'ソート順序',
  `summon_cost` int unsigned NOT NULL COMMENT 'インゲーム召喚コスト',
  `summon_cool_time` int unsigned NOT NULL COMMENT 'インゲーム召喚クールタイム',
  `special_attack_initial_cool_time` int unsigned NOT NULL COMMENT '召喚時の必殺ワザクールタイム',
  `special_attack_cool_time` int unsigned NOT NULL COMMENT '必殺ワザ使用後の必殺ワザクールタイム',
  `min_hp` int unsigned NOT NULL COMMENT '基礎最小ステータス',
  `max_hp` int unsigned NOT NULL COMMENT '基礎最大ステータス',
  `damage_knock_back_count` int unsigned NOT NULL COMMENT '被撃破までのHP減少によるノックバック回数',
  `move_speed` decimal(10,2) NOT NULL COMMENT '移動速度',
  `well_distance` double(8,2) NOT NULL COMMENT '索敵距離',
  `min_attack_power` int unsigned NOT NULL COMMENT '最小攻撃力',
  `max_attack_power` int unsigned NOT NULL COMMENT '最大攻撃力',
  `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstAbilityId',
  `ability_unlock_rank1` int NOT NULL COMMENT '開放ランク',
  `mst_unit_ability_id2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リレーション向けMstAbilityId',
  `ability_unlock_rank2` int NOT NULL COMMENT '開放ランク',
  `mst_unit_ability_id3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リレーション向けMstAbilityId',
  `ability_unlock_rank3` int NOT NULL COMMENT '開放ランク',
  `is_encyclopedia_special_attack_position_right` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '図鑑画面で必殺ワザ再生時にキャラを右寄りにするかフラグ',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラ設定';

-- Table: mst_units_i18n
CREATE TABLE `mst_units_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リレーション向けMstUnitId',
  `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja' COMMENT '言語設定',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名前',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '詳細',
  `detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '情報詳細',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラ設定の多言語設定';

-- Table: mst_user_level_bonus_groups
CREATE TABLE `mst_user_level_bonus_groups` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_user_level_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬グループID',
  `resource_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'タイプ対象のID',
  `resource_amount` int NOT NULL COMMENT '報酬数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `level_bonus_group_index` (`mst_user_level_bonus_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーレベル報酬のグルーピング設定';

-- Table: mst_user_level_bonuses
CREATE TABLE `mst_user_level_bonuses` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `level` int NOT NULL COMMENT 'プレイヤーレベル',
  `mst_user_level_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬グループID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_user_level_bonuses_level_unique` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーレベルごとの報酬設定';

-- Table: mst_user_levels
CREATE TABLE `mst_user_levels` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プレイヤーレベルID',
  `level` int NOT NULL COMMENT 'プレイヤーレベル',
  `stamina` int NOT NULL COMMENT '時間回復最大値のスタミナ',
  `exp` bigint NOT NULL COMMENT 'レベルアップに必要な累計経験値',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーレベル設定';

-- Table: mst_white_words
CREATE TABLE `mst_white_words` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'id',
  `word` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ホワイトワード',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='NGワードから除外されるホワイトワード設定マスターテーブル';

-- Table: opr_asset_release_controls
CREATE TABLE `opr_asset_release_controls` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'バージョン指定',
  `platform` int DEFAULT '0' COMMENT 'プラットフォーム指定',
  `branch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ブランチ名',
  `hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コミットハッシュ',
  `version_no` int NOT NULL DEFAULT '0' COMMENT 'バージョンナンバー',
  `release_at` timestamp NOT NULL COMMENT 'リリース予定日時',
  `release_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リリース内容のメモ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  KEY `idx_release_at` (`release_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アセット配信制御設定';

-- Table: opr_asset_release_versions
CREATE TABLE `opr_asset_release_versions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` int unsigned NOT NULL COMMENT 'リリースキー',
  `git_revision` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのリビジョン',
  `git_branch` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのカレントブランチ',
  `catalog_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'AddressableAssetをビルドした時のCatalogハッシュ値',
  `platform` enum('1','2') COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `build_client_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドクライアントバージョン',
  `asset_total_byte_size` bigint unsigned NOT NULL COMMENT 'アセット合計容量',
  `catalog_byte_size` bigint unsigned NOT NULL COMMENT 'カタログ容量',
  `catalog_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'カタログ名',
  `catalog_hash_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'カタログハッシュファイル名',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='アセット配信バージョン情報設定';

-- Table: opr_asset_releases
CREATE TABLE `opr_asset_releases` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` int unsigned NOT NULL COMMENT 'リリースキー',
  `platform` enum('1','2') COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_asset_release_versions.id',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `release_key_platform_unique` (`release_key`,`platform`),
  KEY `platform_enabled_index` (`platform`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='リリース済みアセット情報設定';

-- Table: opr_campaigns
CREATE TABLE `opr_campaigns` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `campaign_type` enum('Stamina','Exp','ArtworkFragment','ItemDrop','CoinDrop','ChallengeCount') COLLATE utf8mb4_bin NOT NULL COMMENT 'キャンペーンタイプ',
  `target_type` enum('NormalQuest','EnhanceQuest','EventQuest','PvP','AdventBattle') COLLATE utf8mb4_bin NOT NULL COMMENT 'キャンペーン対象タイプ',
  `difficulty` enum('Normal','Hard','Extra') COLLATE utf8mb4_bin NOT NULL COMMENT '難易度',
  `target_id_type` enum('Quest','Series') COLLATE utf8mb4_bin NOT NULL COMMENT '指定するIDのタイプ',
  `target_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_quests.idかmst_series.id',
  `effect_value` smallint unsigned NOT NULL COMMENT '効果値',
  `asset_key` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '対象となるクエストID',
  `start_at` timestamp NOT NULL COMMENT 'キャンペーン開始日時',
  `end_at` timestamp NOT NULL COMMENT 'キャンペーン終了日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='インゲームコンテンツに対するキャンペーン設定';

-- Table: opr_campaigns_i18n
CREATE TABLE `opr_campaigns_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `opr_campaign_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_campaigns.id',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '詳細',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_opr_campaign_id_language` (`opr_campaign_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='キャンペーン説明などの多言語設定';

-- Table: opr_gacha_display_units_i18n
CREATE TABLE `opr_gacha_display_units_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '表示対象のガシャ(opr_gachas.id)',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '表示するキャラ(mst_units.id)',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL COMMENT '言語情報',
  `sort_order` int NOT NULL DEFAULT '0' COMMENT 'キャラの表示順(昇順)',
  `description` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '表示キャラごとの文言',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_gacha_unit_language` (`opr_gacha_id`,`mst_unit_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガチャ画面に表示するユニット情報の多言語情報';

-- Table: opr_gacha_prizes
CREATE TABLE `opr_gacha_prizes` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '同じ抽選テーブルとしてまとめるグループID',
  `resource_type` enum('Coin','Unit','Item') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ガシャの消費リソースのタイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ガシャの消費リソースid',
  `resource_amount` int unsigned DEFAULT NULL COMMENT 'ガシャの消費リソース量',
  `weight` bigint unsigned NOT NULL DEFAULT '1' COMMENT '出現比重',
  `pickup` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'ピックアップ対象',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id_resource_type_resource_id_unique` (`group_id`,`resource_type`,`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガシャの排出物設定';

-- Table: opr_gacha_uppers
CREATE TABLE `opr_gacha_uppers` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `upper_group` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '天井設定区分',
  `upper_type` enum('MaxRarity','Pickup') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'MaxRarity' COMMENT '天井タイプ',
  `count` int unsigned NOT NULL COMMENT '天井を保証する回数',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `upper_type_step_number_unique` (`upper_group`,`upper_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガシャの天井設定';

-- Table: opr_gacha_use_resources
CREATE TABLE `opr_gacha_use_resources` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gachas.id',
  `cost_type` enum('Diamond','PaidDiamond','Free','Item','Ad') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Diamond' COMMENT 'ガシャで使用するコストのタイプ',
  `cost_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '消費リソースID',
  `cost_num` int unsigned NOT NULL DEFAULT '1' COMMENT '一回で必要なアイテムの個数',
  `draw_count` int unsigned NOT NULL DEFAULT '1' COMMENT 'リソースを1回分消費して回せる回数',
  `cost_priority` int unsigned NOT NULL DEFAULT '1' COMMENT '使用するコストの優先度設定',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `opr_gacha_id_cost_type_draw_count_unique` (`opr_gacha_id`,`cost_type`,`draw_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガシャを引くために必要なリソースの設定';

-- Table: opr_gachas
CREATE TABLE `opr_gachas` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `gacha_type` enum('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal','Tutorial') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ガシャのタイプ',
  `upper_group` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '天井設定区分',
  `enable_ad_play` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で回せるか',
  `enable_add_ad_play_upper` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で天井を動かすか',
  `ad_play_interval_time` int unsigned DEFAULT NULL COMMENT '広告で回すことができるインターバル時間(設定単位は分)',
  `multi_draw_count` int unsigned NOT NULL DEFAULT '1' COMMENT 'N連の指定',
  `multi_fixed_prize_count` smallint unsigned DEFAULT '0' COMMENT 'N連の確定枠数',
  `daily_play_limit_count` int unsigned DEFAULT NULL COMMENT '１日に回すことができる上限数',
  `total_play_limit_count` int unsigned DEFAULT NULL COMMENT '回すことができる上限数',
  `daily_ad_limit_count` int unsigned DEFAULT NULL COMMENT '1日に広告で回すことができる上限数',
  `total_ad_limit_count` int unsigned DEFAULT NULL COMMENT '広告で回すことができる上限数',
  `prize_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gacha_prizes.group_id',
  `fixed_prize_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '確定枠(opr_gacha_prizes.group_id)',
  `appearance_condition` enum('Always','HasTicket') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Always' COMMENT '登場条件',
  `unlock_condition_type` enum('None','MainPartTutorialComplete') COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '開放条件タイプ',
  `unlock_duration_hours` smallint unsigned DEFAULT NULL COMMENT '条件達成からの開放時間',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `display_mst_unit_id` text COLLATE utf8mb4_bin COMMENT '表示に使用するピックアップユニットIDを指定する',
  `display_information_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ガチャ詳細用お知らせID',
  `display_gacha_caution_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'ガシャ注意事項のid（adm_gacha_cautions.id）',
  `gacha_priority` int NOT NULL DEFAULT '1' COMMENT 'バナー表示順',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガシャの基本設定';

-- Table: opr_gachas_i18n
CREATE TABLE `opr_gachas_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gachas.id',
  `language` enum('ja','en','zh-Hant') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語情報',
  `name` text COLLATE utf8mb4_bin COMMENT 'ガチャ名',
  `description` text COLLATE utf8mb4_bin COMMENT 'ガチャ説明',
  `max_rarity_upper_description` varchar(255) COLLATE utf8mb4_bin DEFAULT '' COMMENT '最高レアリティ天井の文言',
  `pickup_upper_description` varchar(255) COLLATE utf8mb4_bin DEFAULT '' COMMENT 'ピックアップ天井の文言',
  `fixed_prize_description` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '確定枠の表示文言',
  `banner_url` text COLLATE utf8mb4_bin COMMENT 'バナーURL',
  `logo_asset_key` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `logo_banner_url` text COLLATE utf8mb4_bin COMMENT '詳細へ飛んだ後のロゴバナーurl',
  `gacha_background_color` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ガチャ背景色',
  `gacha_banner_size` enum('SizeM','SizeL') COLLATE utf8mb4_bin NOT NULL DEFAULT 'SizeM' COMMENT 'ガチャバナーサイズ',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `opr_gacha_id_unique` (`opr_gacha_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ガシャ名などの多言語設定';

-- Table: opr_master_release_controls
CREATE TABLE `opr_master_release_controls` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `release_key` bigint NOT NULL COMMENT 'リリースキー',
  `git_revision` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターデータのコミットハッシュ',
  `release_at` timestamp NOT NULL COMMENT 'リリース予定日時',
  `release_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リリース内容のメモ',
  `client_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアント共通データのハッシュ値',
  `zh-Hant_client_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント多言語データ（繁体字中国語）のハッシュ値',
  `en_client_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント多言語データ（英語）のハッシュ値',
  `ja_client_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント多言語データ（日本語）のハッシュ値',
  `client_opr_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアント運用データのハッシュ値',
  `zh-Hant_client_opr_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント運用多言語データ（繁体字中国語）のハッシュ値',
  `en_client_opr_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント運用多言語データ（英語）のハッシュ値',
  `ja_client_opr_i18n_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアント運用多言語データ（日本語）のハッシュ値',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `git_revision_release_key_client_data_hash_unique` (`git_revision`,`release_key`,`client_data_hash`),
  KEY `idx_release_at` (`release_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='マスター配信制御設定';

-- Table: opr_master_release_versions
CREATE TABLE `opr_master_release_versions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` int unsigned NOT NULL COMMENT 'リリースキー',
  `git_revision` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '適用したGitリビジョン',
  `master_scheme_version` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'マスターデータのテーブルスキームのhash化した値',
  `data_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '全ての実データを一意に識別できるハッシュ値',
  `server_db_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'サーバーDBのハッシュ値',
  `client_mst_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアントマスターデータのハッシュ値',
  `client_mst_data_i18n_ja_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアントマスターデータ多言語（日本語）のハッシュ値',
  `client_mst_data_i18n_en_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアントマスターデータ多言語（英語）のハッシュ値',
  `client_mst_data_i18n_zh_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアントマスターデータ多言語（繁体字中国語）のハッシュ値',
  `client_opr_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント運用データのハッシュ値',
  `client_opr_data_i18n_ja_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント運用データ多言語（日本語）のハッシュ値',
  `client_opr_data_i18n_en_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント運用データ多言語（英語）のハッシュ値',
  `client_opr_data_i18n_zh_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント運用データ多言語（繁体字中国語）のハッシュ値',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='マスター配信バージョン情報設定';

-- Table: opr_master_releases
CREATE TABLE `opr_master_releases` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `release_key` int unsigned NOT NULL COMMENT 'リリースキー',
  `enabled` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_master_release_versions.id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `opr_master_releases_release_key_unique` (`release_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='リリース済みマスター情報設定';

-- Table: opr_products
CREATE TABLE `opr_products` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_products.id',
  `product_type` enum('diamond','pack','pass') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '商品タイプ',
  `purchasable_count` int unsigned DEFAULT NULL COMMENT '購入可能回数',
  `paid_amount` bigint NOT NULL DEFAULT '0' COMMENT '配布する有償一次通貨',
  `display_priority` int unsigned NOT NULL COMMENT '表示優先度',
  `start_date` timestamp NOT NULL COMMENT '販売開始日時',
  `end_date` timestamp NOT NULL COMMENT '販売終了日時',
  `release_key` int NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  PRIMARY KEY (`id`),
  KEY `mst_store_product_id_index` (`mst_store_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーに販売する実際の商品を管理する\n1mst_store_productに対して複数';

-- Table: opr_products_i18n
CREATE TABLE `opr_products_i18n` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `opr_product_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '対象のプロダクト(opr_products.id)',
  `language` enum('ja') COLLATE utf8mb4_bin NOT NULL COMMENT '言語情報',
  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'アセットキー',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_opr_product_id_language` (`opr_product_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ユーザーに販売する実際の商品の多言語テーブル';


-- =============================================================================
-- Database: mng (mng)
-- =============================================================================
-- Table: mng_asset_release_versions
CREATE TABLE `mng_asset_release_versions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `git_revision` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのリビジョン',
  `git_branch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのカレントブランチ',
  `catalog_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'AddressableAssetをビルドした時のCatalogハッシュ値',
  `platform` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `build_client_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `asset_total_byte_size` bigint unsigned NOT NULL,
  `catalog_byte_size` bigint unsigned NOT NULL,
  `catalog_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `catalog_hash_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_asset_releases
CREATE TABLE `mng_asset_releases` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `platform` enum('1','2') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_asset_release_versions.id',
  `client_compatibility_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'クライアント互換性バージョン',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'メモ欄',
  `start_at` timestamp NULL DEFAULT NULL COMMENT '開始日時',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `release_key_platform_unique` (`release_key`,`platform`),
  KEY `platform_enabled_index` (`platform`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_client_versions
CREATE TABLE `mng_client_versions` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'UUID',
  `client_version` varchar(32) COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアントバージョン',
  `platform` int NOT NULL COMMENT 'プラットフォーム',
  `is_force_update` tinyint NOT NULL COMMENT '強制アップデートするか',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_client_version_platform` (`client_version`,`platform`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='クライアントバージョン管理';

-- Table: mng_content_closes
CREATE TABLE `mng_content_closes` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '管理コンテンツクローズID',
  `content_type` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'コンテンツタイプ',
  `content_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'コンテンツID（ガチャID、ショップIDなど。null = 全コンテンツ）',
  `start_at` timestamp NOT NULL COMMENT 'クローズ開始時間',
  `end_at` timestamp NOT NULL COMMENT 'クローズ終了時間',
  `is_valid` tinyint NOT NULL DEFAULT '1' COMMENT '有効フラグ',
  PRIMARY KEY (`id`),
  KEY `idx_content_type_content_id` (`content_type`,`content_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_deleted_my_ids
CREATE TABLE `mng_deleted_my_ids` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `my_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'MyID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_my_id` (`my_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_in_game_notices
CREATE TABLE `mng_in_game_notices` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `adm_promotion_tag_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '昇格タグID(adm_promotion_tags.id)',
  `display_type` enum('BasicBanner','Dialog') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '表示モード',
  `enable` tinyint unsigned NOT NULL COMMENT '有効フラグ',
  `priority` int unsigned NOT NULL COMMENT '表示優先度',
  `display_frequency_type` enum('Always','Daily','Weekly','Monthly','Once') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '表示頻度タイプ',
  `destination_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先タイプ',
  `destination_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先情報',
  `destination_path_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先詳細情報',
  `start_at` timestamp NOT NULL COMMENT '掲載開始日時',
  `end_at` timestamp NOT NULL COMMENT '掲載終了日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_in_game_notices_i18n
CREATE TABLE `mng_in_game_notices_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `mng_in_game_notice_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mng_in_game_notices.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'タイトル',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '本文テキスト',
  `banner_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'バナーURL',
  `button_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ボタンに表示するテキスト',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_jump_plus_reward_schedules
CREATE TABLE `mng_jump_plus_reward_schedules` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `adm_promotion_tag_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '昇格タグID(adm_promotion_tags.id)',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mng_jump_plus_rewards.group_id',
  `start_at` timestamp NOT NULL COMMENT '開始日時',
  `end_at` timestamp NOT NULL COMMENT '終了日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ジャンプ+連携報酬のスケジュール';

-- Table: mng_jump_plus_rewards
CREATE TABLE `mng_jump_plus_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬のグルーピングID',
  `resource_type` enum('FreeDiamond','Coin','Item','Unit','Emblem') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT '報酬ID',
  `resource_amount` int NOT NULL COMMENT '報酬の個数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='ジャンプ+連携報酬設定';

-- Table: mng_master_release_versions
CREATE TABLE `mng_master_release_versions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `git_revision` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '適用したGitリビジョン',
  `master_schema_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'マスターデータのテーブルスキームのhash化した値',
  `data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '全ての実データを一意に識別できるハッシュ値',
  `server_db_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_ja_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_en_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_zh_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_ja_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_en_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_zh_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_master_releases
CREATE TABLE `mng_master_releases` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `enabled` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_master_release_versions.id',
  `client_compatibility_version` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント互換性バージョン',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'メモ欄',
  `start_at` timestamp NULL DEFAULT NULL COMMENT '開始日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opr_master_releases_release_key_unique` (`release_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- Table: mng_message_rewards
CREATE TABLE `mng_message_rewards` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mng_message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mng_messages.id',
  `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem','Stamina','Unit') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'リソースタイプ',
  `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'リソースID',
  `resource_amount` int unsigned DEFAULT NULL COMMENT 'リソース数量',
  `display_order` int unsigned NOT NULL COMMENT '表示順',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mng_message_id_display_order` (`mng_message_id`,`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='メッセージ報酬テーブル';

-- Table: mng_messages
CREATE TABLE `mng_messages` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `start_at` timestamp NOT NULL COMMENT '配布開始時日時',
  `expired_at` timestamp NOT NULL COMMENT '表示期限日時',
  `type` enum('All','Individual') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '配布種別',
  `account_created_start_at` timestamp NULL DEFAULT NULL COMMENT '全体配布条件とするアカウント作成日時(開始)',
  `account_created_end_at` timestamp NULL DEFAULT NULL COMMENT '全体配布条件とするアカウント作成日時(終了)',
  `add_expired_days` int NOT NULL COMMENT 'ユーザー受け取り日時加算日数',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='メッセージ管理テーブル';

-- Table: mng_messages_i18n
CREATE TABLE `mng_messages_i18n` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ID',
  `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
  `mng_message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mng_messages.id',
  `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
  `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'タイトル',
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '本文',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mng_message_id_language` (`mng_message_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='メッセージ多言語テーブル';

