-- =============================================================================
-- User Tables DDL (usr, log, sys)
-- =============================================================================


-- =============================================================================
-- Database: usr (TiDB: local, prefix: usr_)
-- =============================================================================
-- Table: usr_advent_battle_sessions
CREATE TABLE `usr_advent_battle_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_advent_battles.id',
  `is_valid` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0:挑戦していない, 1:挑戦中',
  `is_challenge_ad` tinyint NOT NULL DEFAULT '0' COMMENT '広告視聴による挑戦か',
  `party_no` int unsigned NOT NULL DEFAULT '0' COMMENT '挑戦パーティ番号',
  `battle_start_at` timestamp NOT NULL COMMENT 'バトル開始日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='降臨バトルのインゲームセッション管理';

-- Table: usr_advent_battles
CREATE TABLE `usr_advent_battles` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_advent_battles.id',
  `max_score` bigint unsigned NOT NULL DEFAULT '0' COMMENT '最高スコア',
  `total_score` bigint unsigned NOT NULL DEFAULT '0' COMMENT '累計スコア',
  `challenge_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(リセットなし)',
  `reset_challenge_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(デイリーリセット対象)',
  `reset_ad_challenge_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '広告視聴での挑戦回数(デイリーリセット対象)',
  `clear_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'クリア回数',
  `max_received_max_score_reward` bigint unsigned NOT NULL DEFAULT '0' COMMENT '受取済みの最高スコア報酬の最高スコア数値',
  `received_rank_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_advent_battle_reward_groups.id',
  `received_raid_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_advent_battle_reward_groups.id',
  `is_ranking_reward_received` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '順位報酬、またはランク報酬受け取り済みか',
  `max_score_party` json DEFAULT NULL COMMENT '最高スコア時のパーティ情報',
  `is_excluded_ranking` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'ランキングから除外されているか',
  `latest_reset_at` timestamp NULL DEFAULT NULL COMMENT 'リセット日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_advent_battle_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='降臨バトルのステータス管理';

-- Table: usr_artwork_fragments
CREATE TABLE `usr_artwork_fragments` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
  `mst_artwork_fragment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_artwork_fragment_id`) /*T![clustered_index] CLUSTERED */,
  KEY `usr_artwork_fragments_usr_user_id_mst_artwork_id_index` (`usr_user_id`,`mst_artwork_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持している原画のかけら';

-- Table: usr_artworks
CREATE TABLE `usr_artworks` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
  `is_new_encyclopedia` tinyint NOT NULL DEFAULT '1' COMMENT '新規獲得フラグ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_artwork_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持している原画';

-- Table: usr_cheat_sessions
CREATE TABLE `usr_cheat_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コンテンツのタイプ',
  `target_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '降臨バトルの場合はmst_advent_battles.id',
  `party_status` json DEFAULT NULL COMMENT 'パーティステータス',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='チート判定に使用するインゲーム情報の一時保存テーブル';

-- Table: usr_comeback_bonus_progresses
CREATE TABLE `usr_comeback_bonus_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_comeback_bonus_schedule_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_comeback_bonus_schedules.id',
  `start_count` int unsigned NOT NULL DEFAULT '1' COMMENT '開始回数',
  `progress` int unsigned NOT NULL COMMENT 'ログイン回数進捗',
  `latest_update_at` timestamp NULL DEFAULT NULL COMMENT 'ログイン更新日時',
  `start_at` timestamp NOT NULL COMMENT '受取開始日時',
  `end_at` timestamp NOT NULL COMMENT '受取終了日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_comeback_bonus_schedule_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='カムバックボーナス進捗管理';

-- Table: usr_condition_packs
CREATE TABLE `usr_condition_packs` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_pack_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
  `start_date` timestamp NOT NULL COMMENT '購入可能期間開始日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_pack_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='条件パック管理';

-- Table: usr_currency_frees
CREATE TABLE `usr_currency_frees` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `ingame_amount` bigint NOT NULL DEFAULT '0' COMMENT 'ゲーム内・配布などで取得した無償一次通貨の所持数',
  `bonus_amount` bigint NOT NULL DEFAULT '0' COMMENT 'ショップ販売の追加付与で取得した無償一次通貨の所持数\n変動単価性の場合は発生しない',
  `reward_amount` bigint NOT NULL DEFAULT '0' COMMENT '広告視聴などで発生する報酬から取得した無償一次通貨の所持数',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '削除日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する無償一次通貨の詳細';

-- Table: usr_currency_paids
CREATE TABLE `usr_currency_paids` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `seq_no` bigint unsigned NOT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `left_amount` bigint NOT NULL COMMENT '同一単価・通貨での残所持数',
  `purchase_price` decimal(20,6) NOT NULL COMMENT '購入時のストアから送られてくる購入価格',
  `purchase_amount` bigint NOT NULL COMMENT '購入時に取得した有償一次通貨数',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `currency_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `receipt_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'このレコードを生成した購入レシートID（購入の場合）',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '削除日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`seq_no`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `platform_receipt_unique_id_unique` (`billing_platform`,`receipt_unique_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`),
  KEY `user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する有償一次通貨の詳細';

-- Table: usr_currency_summaries
CREATE TABLE `usr_currency_summaries` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `paid_amount_apple` bigint NOT NULL DEFAULT '0' COMMENT 'AppStoreで購入した有償一次通貨の所持数',
  `paid_amount_google` bigint NOT NULL DEFAULT '0' COMMENT 'GooglePlayで購入した有償一次通貨の所持数',
  `free_amount` bigint NOT NULL DEFAULT '0' COMMENT '無償一次通貨の所持数',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '削除日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する通貨の集約データ';

-- Table: usr_device_link_passwords
CREATE TABLE `usr_device_link_passwords` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `auth_id` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '認証ID',
  `auth_password` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '認証パスワード',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `usr_device_link_passwords_auth_id_unique` (`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デバイス連携パスワード管理';

-- Table: usr_device_link_socials
CREATE TABLE `usr_device_link_socials` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `auth_type` tinyint NOT NULL COMMENT '認証タイプ',
  `auth_token` varchar(256) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '認証トークン',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`auth_type`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `auth_type_auth_token` (`auth_type`,`auth_token`),
  UNIQUE KEY `uk_usr_user_id_auth_type` (`usr_user_id`,`auth_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デバイス連携ソーシャルアカウント管理';

-- Table: usr_devices
CREATE TABLE `usr_devices` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'デバイスのUUID',
  `bnid_linked_at` timestamp NULL DEFAULT NULL COMMENT 'BNID連携日時',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `uuid` (`uuid`),
  KEY `idx_usr_user_id` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーデバイス管理';

-- Table: usr_emblems
CREATE TABLE `usr_emblems` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_emblems.id',
  `is_new_encyclopedia` tinyint NOT NULL DEFAULT '1' COMMENT '新規獲得フラグ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_emblem_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='エンブレム所持管理';

-- Table: usr_enemy_discoveries
CREATE TABLE `usr_enemy_discoveries` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_enemy_character_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_enemy_characters.id',
  `is_new_encyclopedia` tinyint NOT NULL DEFAULT '1' COMMENT '新規獲得フラグ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_enemy_character_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='発見した敵キャラ情報';

-- Table: usr_exchange_lineups
CREATE TABLE `usr_exchange_lineups` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_exchange_lineup_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_exchange_lineups.id',
  `mst_exchange_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_exchanges.id',
  `trade_count` int unsigned NOT NULL DEFAULT '0' COMMENT '累計交換回数',
  `reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usr_user_id`,`mst_exchange_lineup_id`,`mst_exchange_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー交換履歴テーブル';

-- Table: usr_gacha_uppers
CREATE TABLE `usr_gacha_uppers` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `upper_group` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '天井設定区分',
  `upper_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '天井タイプ',
  `count` int unsigned NOT NULL DEFAULT '0' COMMENT '天井を保証する回数 リセット条件に合致した場合カウントは0に戻る',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`upper_group`,`upper_type`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ガシャの天井管理';

-- Table: usr_gachas
CREATE TABLE `usr_gachas` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_gachas.id',
  `ad_played_at` timestamp NULL DEFAULT NULL COMMENT '広告で回した時間',
  `played_at` timestamp NULL DEFAULT NULL COMMENT '回した時間',
  `ad_count` int unsigned NOT NULL DEFAULT '0' COMMENT '広告でガチャを回した回数',
  `ad_daily_count` int unsigned NOT NULL DEFAULT '0' COMMENT '広告で本日ガチャを回した回数',
  `count` int unsigned NOT NULL DEFAULT '0' COMMENT 'ガチャを回した回数',
  `daily_count` int unsigned NOT NULL DEFAULT '0' COMMENT '日次でガチャを回した回数',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT '終了日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`opr_gacha_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ガシャ管理';

-- Table: usr_idle_incentives
CREATE TABLE `usr_idle_incentives` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `diamond_quick_receive_count` int unsigned NOT NULL DEFAULT '0' COMMENT '一次通貨でのクイック獲得回数',
  `ad_quick_receive_count` int unsigned NOT NULL DEFAULT '0' COMMENT '広告でのクイック獲得回数',
  `reward_mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '探索報酬を決めるステージID(mst_stages.id)',
  `idle_started_at` timestamp NOT NULL COMMENT '放置開始時間',
  `diamond_quick_receive_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT '一次通貨でクイック獲得を実行した時刻',
  `ad_quick_receive_at` timestamp NOT NULL COMMENT '広告でクイック獲得を実行した時刻',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索ステータス管理';

-- Table: usr_item_trades
CREATE TABLE `usr_item_trades` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_items.id',
  `trade_amount` bigint NOT NULL DEFAULT '0' COMMENT '通算交換量（リセットなし）',
  `reset_trade_amount` bigint NOT NULL DEFAULT '0' COMMENT '交換量（リセットあり）',
  `trade_amount_reset_at` timestamp NOT NULL COMMENT '交換量をリセットした日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_item_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アイテムの交換情報管理';

-- Table: usr_items
CREATE TABLE `usr_items` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'mst_items.id',
  `amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '名前',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_item_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アイテム所持管理';

-- Table: usr_jump_plus_rewards
CREATE TABLE `usr_jump_plus_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mng_jump_plus_reward_schedule_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mng_jump_plus_reward_schedules.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usr_user_id`,`mng_jump_plus_reward_schedule_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ジャンプ+連携報酬の受取管理';

-- Table: usr_messages
CREATE TABLE `usr_messages` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mng_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mng_messages.id',
  `message_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'メッセージの送信元',
  `reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リワードグループID',
  `resource_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬タイプ',
  `resource_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬リソースID',
  `resource_amount` int unsigned DEFAULT NULL COMMENT '報酬の個数',
  `is_received` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '受け取り済みフラグ',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'opr_messages.idがない時に使用するタイトル',
  `body` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'opr_messages.idがない時に使用する本文',
  `opened_at` timestamp NULL DEFAULT NULL COMMENT '読んだ日付',
  `received_at` timestamp NULL DEFAULT NULL COMMENT '報酬を受け取った日付',
  `expired_at` timestamp NULL DEFAULT NULL COMMENT '受け取り期限日時 nullの場合はなしとして扱う',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `usr_user_id_expired_at_index` (`usr_user_id`,`expired_at`),
  KEY `usr_user_id_index` (`usr_user_id`),
  KEY `user_id_reward_group_id_index` (`usr_user_id`,`reward_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='メールボックスのメール管理';

-- Table: usr_mission_daily_bonuses
CREATE TABLE `usr_mission_daily_bonuses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_daily_bonus_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_daily_bonuses.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_mission_daily_bonus_id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_daily_bonuses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='デイリーボーナスのステータス管理';

-- Table: usr_mission_event_daily_bonus_progresses
CREATE TABLE `usr_mission_event_daily_bonus_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_event_daily_bonus_schedule_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_event_daily_bonus_schedules.id',
  `progress` int unsigned NOT NULL COMMENT 'ログイン回数進捗',
  `latest_update_at` timestamp NULL DEFAULT NULL COMMENT 'ログイン更新日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_mission_event_daily_bonus_schedule_id`) /*T![clustered_index] CLUSTERED */,
  KEY `usr_mission_event_daily_bonus_progresses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='イベントミッションデイリーボーナス進捗管理';

-- Table: usr_mission_event_daily_bonuses
CREATE TABLE `usr_mission_event_daily_bonuses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_event_daily_bonus_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_event_daily_bonuses.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_mission_event_daily_bonus_id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_event_daily_bonuses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='イベントデイリーボーナスのステータス管理';

-- Table: usr_mission_events
CREATE TABLE `usr_mission_events` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mission_type` tinyint NOT NULL COMMENT 'ミッションタイプ',
  `mst_mission_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'イベントミッションのマスタデータのID',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT 'ステータス',
  `is_open` tinyint NOT NULL DEFAULT '0' COMMENT '開放ステータス',
  `progress` bigint NOT NULL DEFAULT '0' COMMENT '進捗値',
  `unlock_progress` bigint NOT NULL DEFAULT '0' COMMENT '開放進捗値',
  `latest_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mission_type`,`mst_mission_id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_status` (`usr_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='イベントミッションのステータス管理';

-- Table: usr_mission_limited_terms
CREATE TABLE `usr_mission_limited_terms` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_limited_term_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_limited_terms.id',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT 'ステータス',
  `is_open` tinyint NOT NULL DEFAULT '0' COMMENT '開放ステータス',
  `progress` bigint NOT NULL DEFAULT '0' COMMENT '進捗値',
  `latest_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_mission_limited_term_id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_status` (`usr_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='期間限定ミッションのステータス管理';

-- Table: usr_mission_normals
CREATE TABLE `usr_mission_normals` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mission_type` tinyint unsigned NOT NULL COMMENT 'ミッションタイプのenum値',
  `mst_mission_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションのマスタデータのID(mst_mission_xxxs.id)',
  `status` tinyint unsigned NOT NULL COMMENT 'ミッションステータス',
  `is_open` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '開放ステータス 0:未開放,1:開放済',
  `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '進捗値',
  `unlock_progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '開放進捗値',
  `latest_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mission_type`,`mst_mission_id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_status` (`usr_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ノーマル系ミッションのユーザー進捗管理';

-- Table: usr_mission_statuses
CREATE TABLE `usr_mission_statuses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `beginner_mission_status` smallint unsigned NOT NULL DEFAULT '0' COMMENT '初心者ミッション未クリア: 0 初心者ミッションクリア: 1',
  `latest_mst_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '前回即時判定をしたときのマスタデータハッシュ値',
  `mission_unlocked_at` timestamp NULL DEFAULT NULL COMMENT 'ミッション解放日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ミッションステータス管理';

-- Table: usr_os_platforms
CREATE TABLE `usr_os_platforms` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usr_user_id`,`os_platform`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのOSプラットフォーム情報';

-- Table: usr_outpost_enhancements
CREATE TABLE `usr_outpost_enhancements` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_outposts.id',
  `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_outpost_enhancements.id',
  `level` int unsigned NOT NULL COMMENT 'レベル',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_outpost_enhancement_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化ステータス管理';

-- Table: usr_outposts
CREATE TABLE `usr_outposts` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_outposts.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artworks.id',
  `is_used` tinyint NOT NULL DEFAULT '0' COMMENT '使用中かどうか',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_outpost_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲートステータス管理';

-- Table: usr_parties
CREATE TABLE `usr_parties` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `party_no` int unsigned NOT NULL COMMENT 'パーティ番号',
  `party_name` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パーティ名',
  `usr_unit_id_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1スロット目のユーザーユニットID',
  `usr_unit_id_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '2スロット目のユーザーユニットID',
  `usr_unit_id_3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '3スロット目のユーザーユニットID',
  `usr_unit_id_4` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '4スロット目のユーザーユニットID',
  `usr_unit_id_5` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '5スロット目のユーザーユニットID',
  `usr_unit_id_6` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '6スロット目のユーザーユニットID',
  `usr_unit_id_7` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '7スロット目のユーザーユニットID',
  `usr_unit_id_8` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '8スロット目のユーザーユニットID',
  `usr_unit_id_9` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '9スロット目のユーザーユニットID',
  `usr_unit_id_10` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '10スロット目のユーザーユニットID',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`party_no`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='パーティ編成管理';

-- Table: usr_pvp_sessions
CREATE TABLE `usr_pvp_sessions` (
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `sys_pvp_season_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sys_pvp_seasons.id',
  `is_use_item` tinyint NOT NULL DEFAULT '0' COMMENT 'アイテム使用フラグ (0: 使用しない, 1: 使用する)',
  `party_no` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
  `opponent_my_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '対戦相手のmyId',
  `opponent_pvp_status` json NOT NULL COMMENT '対戦相手のPVPステータス',
  `opponent_score` bigint unsigned NOT NULL DEFAULT '0' COMMENT '対戦相手のスコア',
  `is_valid` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0:挑戦していない, 1:挑戦中',
  `battle_start_at` timestamp NOT NULL COMMENT 'セッション開始日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PVPセッション情報';

-- Table: usr_pvps
CREATE TABLE `usr_pvps` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `sys_pvp_season_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sys_pvp_seasons.id',
  `score` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'スコア',
  `max_received_score_reward` bigint unsigned NOT NULL DEFAULT '0' COMMENT '受取済みの最高スコア報酬のスコア数値',
  `pvp_rank_class_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PVPランク区分',
  `pvp_rank_class_level` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'PVPランク区分レベル',
  `ranking` int DEFAULT NULL COMMENT 'ランキング',
  `is_season_reward_received` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'シーズン報酬受け取り済みか',
  `is_excluded_ranking` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'ランキングから除外されているか',
  `daily_remaining_challenge_count` int unsigned NOT NULL COMMENT '残りアイテム消費なし挑戦可能回数',
  `daily_remaining_item_challenge_count` int unsigned NOT NULL COMMENT '残りアイテム消費あり挑戦可能回数',
  `last_played_at` timestamp NULL DEFAULT NULL COMMENT '最終プレイ日時',
  `latest_reset_at` timestamp NOT NULL COMMENT 'リセット日時',
  `selected_opponent_candidates` json DEFAULT NULL COMMENT '選択した対戦相手の情報リスト',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`usr_user_id`,`sys_pvp_season_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='開催毎の個人PVP情報';

-- Table: usr_received_unit_encyclopedia_rewards
CREATE TABLE `usr_received_unit_encyclopedia_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_unit_encyclopedia_reward_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_unit_encyclopedia_rewards.id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_unit_encyclopedia_reward_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーが受け取った図鑑報酬のID';

-- Table: usr_shop_items
CREATE TABLE `usr_shop_items` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_usersのid',
  `mst_shop_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_shop_itemsのid',
  `trade_count` int unsigned NOT NULL COMMENT '交換回数',
  `trade_total_count` int unsigned NOT NULL DEFAULT '0' COMMENT '累計交換回数',
  `last_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_shop_item_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップアイテム購入状況管理';

-- Table: usr_shop_passes
CREATE TABLE `usr_shop_passes` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_shop_pass_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_shop_passes.id',
  `daily_reward_received_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '毎日報酬を受け取った回数',
  `daily_latest_received_at` timestamp NULL DEFAULT NULL COMMENT '毎日報酬を受け取った日時',
  `start_at` timestamp NOT NULL COMMENT 'パスの開始日時',
  `end_at` timestamp NOT NULL COMMENT 'パスの終了日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_shop_pass_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='購入したパス管理';

-- Table: usr_stage_enhances
CREATE TABLE `usr_stage_enhances` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `clear_count` int unsigned NOT NULL DEFAULT '0' COMMENT '過去通算のクリア回数',
  `reset_challenge_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'リセット以降の通常の挑戦回数',
  `reset_ad_challenge_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'リセット以降の広告視聴による挑戦回数',
  `max_score` bigint unsigned NOT NULL DEFAULT '0' COMMENT '過去通算のスコア最大値',
  `latest_reset_at` timestamp NULL DEFAULT NULL COMMENT 'リセット日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_stage_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='強化クエストステージのステータス管理';

-- Table: usr_stage_events
CREATE TABLE `usr_stage_events` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `clear_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'クリア回数',
  `reset_clear_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'リセットからのクリア回数',
  `reset_ad_challenge_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'リセットからの広告視聴での挑戦回数',
  `reset_clear_time_ms` int unsigned DEFAULT NULL COMMENT '開催期間中のクリアタイム(ミリ秒)',
  `clear_time_ms` int unsigned DEFAULT NULL COMMENT 'クリアタイム(ミリ秒)',
  `latest_reset_at` timestamp NULL DEFAULT NULL COMMENT 'リセット日時',
  `latest_event_setting_end_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00' COMMENT 'mst_stage_event_settings.end_at',
  `last_challenged_at` timestamp NULL DEFAULT NULL COMMENT '最終挑戦日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_stage_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='イベントクエストステージのステータス管理';

-- Table: usr_stage_sessions
CREATE TABLE `usr_stage_sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `is_valid` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'ステージ挑戦中フラグ',
  `party_no` int unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
  `continue_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'コンティニュー回数',
  `daily_continue_ad_count` int unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告コンティニュー回数',
  `is_challenge_ad` tinyint NOT NULL DEFAULT '0' COMMENT '広告視聴による挑戦か',
  `opr_campaign_ids` json DEFAULT NULL COMMENT 'opr_campaigns.idの配列',
  `auto_lap_count` int unsigned NOT NULL DEFAULT '1' COMMENT 'スタミナブースト周回指定',
  `latest_reset_at` timestamp NOT NULL COMMENT 'リセット日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージのインゲームセッション管理';

-- Table: usr_stages
CREATE TABLE `usr_stages` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `clear_count` bigint NOT NULL COMMENT 'クリア回数',
  `clear_time_ms` int unsigned DEFAULT NULL COMMENT 'クリアタイム(ミリ秒)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_stage_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージのステータス管理';

-- Table: usr_store_allowances
CREATE TABLE `usr_store_allowances` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ストアのプロダクトID',
  `mst_store_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_product_id',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay のどちらで購入フローをしようとしているか',
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`billing_platform`,`product_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ購入の許可確認状態';

-- Table: usr_store_infos
CREATE TABLE `usr_store_infos` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `age` int NOT NULL COMMENT '年齢',
  `paid_price` bigint NOT NULL DEFAULT '0' COMMENT '支払ったJPYの金額',
  `renotify_at` timestamp NULL DEFAULT NULL COMMENT '次回年齢再確認する日時\nNULLなら再確認しなくて良い（20歳以上）',
  `total_vip_point` bigint NOT NULL DEFAULT '0' COMMENT '商品購入時に獲得したVIPポイントの合計',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '削除日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ登録情報';

-- Table: usr_store_product_histories
CREATE TABLE `usr_store_product_histories` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `receipt_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォームごとの購入毎ユニークなIDを入れる',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `age` int NOT NULL COMMENT '年齢',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `platform_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム側で定義しているproduct_id',
  `mst_store_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターテーブルのプロダクトID',
  `currency_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントから送られてきた通貨コード',
  `receipt_bundle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
  `receipt_purchase_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた購入トークン',
  `paid_amount` bigint NOT NULL COMMENT '有償一次通貨の付与量',
  `free_amount` bigint NOT NULL COMMENT '無償一次通貨の付与量',
  `purchase_price` decimal(20,6) NOT NULL COMMENT 'ストアから送られてきた実際の購入価格',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT '削除日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `receipt_unique_id_billing_platform_unique` (`receipt_unique_id`,`billing_platform`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ購入履歴';

-- Table: usr_store_products
CREATE TABLE `usr_store_products` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_usersのid',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_productsのid',
  `purchase_count` int unsigned NOT NULL COMMENT '購入回数',
  `purchase_total_count` int unsigned NOT NULL DEFAULT '0' COMMENT '累計購入回数',
  `last_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`product_sub_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショッププロダクトの購入状況管理';

-- Table: usr_temporary_individual_messages
CREATE TABLE `usr_temporary_individual_messages` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mng_message_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mng_messages.id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mng_message_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー個別メッセージ一時保存テーブル';

-- Table: usr_trade_packs
CREATE TABLE `usr_trade_packs` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_pack_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
  `daily_trade_count` int unsigned NOT NULL COMMENT 'デイリー交換回数',
  `last_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `usr_user_id_mst_pack_id_unique` (`usr_user_id`,`mst_pack_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='パックの交換管理テーブル';

-- Table: usr_tutorial_gachas
CREATE TABLE `usr_tutorial_gachas` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `gacha_result_json` json NOT NULL COMMENT 'ガシャ結果の一時保存json',
  `confirmed_at` timestamp NULL DEFAULT NULL COMMENT 'ガシャ結果を確定した日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='チュートリアルガチャ管理';

-- Table: usr_tutorials
CREATE TABLE `usr_tutorials` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_tutorial_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_tutorials.id',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`,`mst_tutorial_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='チュートリアル管理';

-- Table: usr_unit_summaries
CREATE TABLE `usr_unit_summaries` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `grade_level_total_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'UserごとのUnitGradeUp回数',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット要約管理';

-- Table: usr_units
CREATE TABLE `usr_units` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0' COMMENT 'mst_units.id',
  `level` int unsigned NOT NULL DEFAULT '1' COMMENT 'ユニットのレベル',
  `rank` int unsigned NOT NULL DEFAULT '1' COMMENT 'ユニットのランク',
  `grade_level` int unsigned NOT NULL DEFAULT '0' COMMENT 'ユニットのグレード',
  `battle_count` int NOT NULL DEFAULT '0' COMMENT '出撃回数',
  `is_new_encyclopedia` tinyint NOT NULL DEFAULT '1' COMMENT '新規獲得フラグ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `mst_unit_and_usr_id_index` (`usr_user_id`,`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='所持ユニット管理';

-- Table: usr_user_buy_counts
CREATE TABLE `usr_user_buy_counts` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `daily_buy_stamina_ad_count` int NOT NULL DEFAULT '0' COMMENT '1日の広告視聴でのスタミナ購入回数',
  `daily_buy_stamina_ad_at` timestamp NULL DEFAULT NULL COMMENT '1日の広告視聴してスタミナを購入した日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='スタミナ購入状況管理';

-- Table: usr_user_logins
CREATE TABLE `usr_user_logins` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `first_login_at` timestamp NULL DEFAULT NULL COMMENT '初回ログイン日時',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT '最終ログイン日時',
  `hourly_accessed_at` timestamp NOT NULL COMMENT '1時間毎の最初のアクセス日時',
  `login_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'ログイン回数',
  `login_day_count` int NOT NULL DEFAULT '0' COMMENT '生涯累計のログイン日数',
  `login_continue_day_count` int NOT NULL DEFAULT '0' COMMENT '連続ログイン日数。連続ログインが途切れたら0にリセットする。',
  `comeback_day_count` int NOT NULL DEFAULT '0' COMMENT '最終ログインからの復帰にかかった日数',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーログイン回数記録';

-- Table: usr_user_parameters
CREATE TABLE `usr_user_parameters` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `level` int unsigned NOT NULL DEFAULT '1' COMMENT 'ユーザーレベル',
  `exp` bigint NOT NULL DEFAULT '0' COMMENT '経験値',
  `coin` bigint unsigned NOT NULL DEFAULT '0' COMMENT '無償通貨',
  `stamina` int unsigned NOT NULL DEFAULT '0' COMMENT 'スタミナ',
  `stamina_updated_at` timestamp NULL DEFAULT NULL COMMENT 'スタミナ更新タイムスタンプ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='コインなどのユーザーリソース管理';

-- Table: usr_user_profiles
CREATE TABLE `usr_user_profiles` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `my_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'MYID',
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '名前',
  `is_change_name` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '名前を変更したか',
  `birth_date` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '暗号化された生年月日の数字データ',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アバターとして設定したユニットのID',
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'エンブレムID',
  `name_update_at` timestamp NULL DEFAULT NULL COMMENT '名前変更日時のタイムスタンプ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */,
  UNIQUE KEY `usr_user_profiles_my_id_unique` (`my_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザープロフィール管理';

-- Table: usr_users
CREATE TABLE `usr_users` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `status` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'ユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN',
  `tutorial_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'チュートリアルステータス',
  `tos_version` smallint unsigned NOT NULL DEFAULT '0' COMMENT '同意した利用規約のバージョン 同意モジュールを使っているので未使用列',
  `privacy_policy_version` smallint unsigned NOT NULL DEFAULT '0' COMMENT '同意したプライバシーポリシーのバージョン 同意モジュールを使っているので未使用列',
  `global_consent_version` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'グローバルコンセントバージョン',
  `iaa_version` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'iaaバージョン',
  `bn_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'BNIDユーザーID',
  `is_account_linking_restricted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'マルチログイン制限フラグ',
  `client_uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアントUUID',
  `suspend_end_at` timestamp NULL DEFAULT NULL COMMENT '利用停止状態の終了日時',
  `game_start_at` timestamp NOT NULL COMMENT 'ゲーム開始日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `bn_user_id_index` (`bn_user_id`),
  KEY `client_uuid_created_at_index` (`client_uuid`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザー基本情報';


-- =============================================================================
-- Database: log (TiDB: local, prefix: log_)
-- =============================================================================
-- Table: log_ad_free_plays
CREATE TABLE `log_ad_free_plays` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '広告視聴をしたコンテンツ',
  `target_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象コンテンツの識別子',
  `play_at` timestamp NOT NULL COMMENT '広告視聴によって無料プレイした日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`),
  KEY `idx_user_id_and_contents` (`usr_user_id`,`content_type`,`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='広告視聴による無料プレイログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_advent_battle_actions
CREATE TABLE `log_advent_battle_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_advent_battles.id',
  `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストされた降臨バトル関連のAPI',
  `result` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利',
  `party_units` json DEFAULT NULL COMMENT 'ユニットのステータス情報を含めたパーティ情報',
  `used_outpost` json DEFAULT NULL COMMENT '使用したゲート情報',
  `in_game_battle_log` json DEFAULT NULL COMMENT 'インゲームのバトルログ',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='降臨バトル挑戦ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_advent_battle_rewards
CREATE TABLE `log_advent_battle_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `received_reward` json NOT NULL COMMENT '配布報酬情報(変換前情報あり)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='降臨バトル報酬ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_allowances
CREATE TABLE `log_allowances` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ストアのプロダクトID',
  `mst_store_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_product_id',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay のどちらで購入フローをしようとしているか',
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alllowanceの変更契機',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象のallowanceのID',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リクエスト識別IDの種類',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='購入許可ログ';

-- Table: log_app_store_refunds
CREATE TABLE `log_app_store_refunds` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '課金のトランザクションID',
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '返金金額',
  `refunded_at` timestamp NOT NULL COMMENT '返金日時',
  `signed_payload` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '署名付きの返金通知',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `transaction_id_index` (`transaction_id`),
  KEY `refunded_at_index` (`refunded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='AppStoreリファンドログ';

-- Table: log_artwork_fragments
CREATE TABLE `log_artwork_fragments` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_artwork_fragment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画のかけらを入手したコンテンツのタイプ',
  `target_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画のかけらを入手したコンテンツ',
  `is_complete_artwork` smallint unsigned NOT NULL COMMENT '原画が完成したかどうか: 1: 原画が完成した, 0: 原画未完成',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`),
  KEY `idx_user_id_and_contents` (`usr_user_id`,`content_type`,`target_id`),
  KEY `idx_user_id_and_mst_artwork_fragment_id` (`usr_user_id`,`mst_artwork_fragment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_banks
CREATE TABLE `log_banks` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `event_id` enum('100','200','300') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'イベントID',
  `platform_user_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム別識別番号',
  `user_first_created_at` timestamp NOT NULL COMMENT 'ユーザー初回登録日時',
  `user_agent` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーエージェント',
  `os_platform` int NOT NULL COMMENT 'OSプラットフォーム。UserConstantのPLATFORM_XXXの値。',
  `os_version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSバージョン',
  `country_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '国コード',
  `ad_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '広告ID',
  `request_at` timestamp NOT NULL COMMENT 'APIリクエスト日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`),
  KEY `idx_created_at_id` (`created_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BanK KPI用ログ';

-- Table: log_bnid_links
CREATE TABLE `log_bnid_links` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '連携したか解除したかなどのアクション種別',
  `before_bn_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '変更前のBNIDユーザーID',
  `after_bn_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '変更後のBNIDユーザーID',
  `usr_device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'デバイスID',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'OSプラットフォーム',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='BNID連携ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_close_store_transactions
CREATE TABLE `log_close_store_transactions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `platform_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム側で定義しているproduct_id',
  `mst_store_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターテーブルのプロダクトID',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `product_sub_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実際の販売商品名',
  `raw_receipt` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '復号済み生レシートデータ',
  `raw_price_string` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントから送られてきた単価付き購入価格',
  `currency_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `receipt_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ユニークなID',
  `receipt_bundle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `purchase_price` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ストアから送られてきた実際の購入価格',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `log_store_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '失敗したストア購入ログのレコードID',
  `usr_store_product_history_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '失敗したストア商品購入テーブルのレコードID',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ロギング契機',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ロギング契機の日本語名',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ロギング契機に対応するID',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'その他の付与情報 (JSON)',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別IDの種類',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時',
  `updated_at` timestamp NOT NULL COMMENT '更新日時',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `platform_product_id_index` (`platform_product_id`),
  KEY `mst_store_product_id_index` (`mst_store_product_id`),
  KEY `product_sub_id_index` (`product_sub_id`),
  KEY `receipt_unique_id_index` (`receipt_unique_id`),
  KEY `receipt_bundle_id_index` (`receipt_bundle_id`),
  KEY `log_stores_user_id_index` (`log_store_id`),
  KEY `usr_store_product_histories_user_id_index` (`usr_store_product_history_id`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `request_id_index` (`request_id`),
  KEY `nginx_request_id_index` (`nginx_request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: log_coins
CREATE TABLE `log_coins` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Get: 獲得 Use: 消費',
  `before_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動前の量',
  `after_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動後の量',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報値',
  `trigger_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報オプション',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='コイン変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_currency_frees
CREATE TABLE `log_currency_frees` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `logging_no` bigint unsigned NOT NULL COMMENT 'ログ登録番号',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `before_ingame_amount` bigint NOT NULL COMMENT '変更前のゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数',
  `before_bonus_amount` bigint NOT NULL COMMENT '変更前のショップ販売の追加ボーナスで取得した無償一次通貨の数',
  `before_reward_amount` bigint NOT NULL COMMENT '変更前の広告視聴等の報酬で取得した無償一次通貨の数',
  `change_ingame_amount` bigint NOT NULL COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数\n消費の場合は負',
  `change_bonus_amount` bigint NOT NULL COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の数\n消費の場合は負',
  `change_reward_amount` bigint NOT NULL COMMENT '広告視聴等の報酬で取得した無償一次通貨の数\n消費の場合は負',
  `current_ingame_amount` bigint NOT NULL COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の現在の数',
  `current_bonus_amount` bigint NOT NULL COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の現在の数',
  `current_reward_amount` bigint NOT NULL COMMENT '広告視聴等の報酬で取得した無償一次通貨の現在の数',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '無償一次通貨の変動契機',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リクエスト識別IDの種類',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_logging_no_index` (`created_at`,`logging_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='無償一次通貨ログ';

-- Table: log_currency_paids
CREATE TABLE `log_currency_paids` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `seq_no` bigint unsigned NOT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `currency_paid_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動した通貨テーブルのレコードID',
  `receipt_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'このレコードを生成した購入レシートID（購入の場合）',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `query` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'どういう変化が起きたか',
  `purchase_price` decimal(20,6) NOT NULL COMMENT '購入時の価格',
  `purchase_amount` bigint NOT NULL COMMENT '購入時に付与された個数',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `currency_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `before_amount` bigint NOT NULL COMMENT '変更前の有償一次通貨の数',
  `change_amount` bigint NOT NULL COMMENT '取得した有償一次通貨の数\n消費の場合は負',
  `current_amount` bigint NOT NULL COMMENT '変動後現在でユーザーがプラットフォームに所持している有償一次通貨の数\n単価関係ない総数（summaryに入れる数）',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '有償一次通貨の変動契機',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リクエスト識別IDの種類',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `currency_paid_id_index` (`currency_paid_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `receipt_unique_id_index` (`receipt_unique_id`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='有償一次通貨ログ';

-- Table: log_currency_revert_histories
CREATE TABLE `log_currency_revert_histories` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コメント',
  `log_trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガータイプ',
  `log_trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガーID',
  `log_trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガー名',
  `log_trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのそのほかの付与情報',
  `log_request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '対象ログのリクエスト識別IDの種類',
  `log_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのリクエストID',
  `log_created_at` timestamp NOT NULL COMMENT '対象ログの作成日時',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リクエスト識別IDの種類',
  `log_change_paid_amount` bigint NOT NULL COMMENT '変更対象の有償通貨',
  `log_change_free_amount` bigint NOT NULL COMMENT '変更対象の無償通貨',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガータイプ',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガーID',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガー名',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガー詳細',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログ';

-- Table: log_currency_revert_history_free_logs
CREATE TABLE `log_currency_revert_history_free_logs` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `log_currency_revert_history_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_revert_historiessのID',
  `log_currency_free_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実行した際のlog_currency_freesのID',
  `revert_log_currency_free_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_freesの返却対象としたログのID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログと無償一次通貨ログの紐付け';

-- Table: log_currency_revert_history_paid_logs
CREATE TABLE `log_currency_revert_history_paid_logs` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `log_currency_revert_history_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_revert_historiesのID',
  `log_currency_paid_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実行した際のlog_currency_paidsのID',
  `revert_log_currency_paid_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_paidsの返却対象としたログのID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログと有償一次通貨ログの紐付け';

-- Table: log_emblems
CREATE TABLE `log_emblems` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_emblems.id',
  `amount` int unsigned NOT NULL DEFAULT '0' COMMENT '変動数',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報値',
  `trigger_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報オプション',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='エンブレム変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_encyclopedia_rewards
CREATE TABLE `log_encyclopedia_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `received_reward` json NOT NULL COMMENT '配布報酬情報(変換前情報あり)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='図鑑報酬ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_exchange_actions
CREATE TABLE `log_exchange_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_exchange_lineup_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_exchange_lineups.id',
  `mst_exchange_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_exchanges.id',
  `costs` json NOT NULL COMMENT '支払ったコスト情報',
  `rewards` json NOT NULL COMMENT '獲得した報酬情報',
  `trade_count` int unsigned NOT NULL DEFAULT '1' COMMENT '交換個数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_usr_user_id` (`usr_user_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='交換所アクションログテーブル';

-- Table: log_exps
CREATE TABLE `log_exps` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Get: 獲得 Use: 消費',
  `before_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動前の量',
  `after_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動後の量',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報値',
  `trigger_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報オプション',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='EXP変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_gacha_actions
CREATE TABLE `log_gacha_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_gachas.id',
  `cost_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消費したコスト情報',
  `draw_count` int unsigned NOT NULL COMMENT '回した数 (排出数)',
  `max_rarity_upper_count` int unsigned NOT NULL COMMENT 'ガシャを回す前の最高レア天井のカウント',
  `pickup_upper_count` int unsigned NOT NULL COMMENT 'ガシャを回す前のピックアップ天井のカウント',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ガチャ実行ログ';

-- Table: log_gachas
CREATE TABLE `log_gachas` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `opr_gacha_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_gachas.id',
  `result` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ガシャの排出物',
  `cost_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '使用したゲート情報（シリアライズデータ）',
  `draw_count` smallint unsigned NOT NULL COMMENT 'ガシャを引いた回数',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_created_at` (`created_at`),
  KEY `idx_created_at_opr_gacha_id` (`created_at`,`opr_gacha_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ガチャ排出ログ';

-- Table: log_google_play_refunds
CREATE TABLE `log_google_play_refunds` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '課金のトランザクションID',
  `price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '返金金額',
  `refunded_at` timestamp NOT NULL COMMENT '返金日時',
  `purchase_token` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '署名付きの返金通知',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `transaction_id_index` (`transaction_id`),
  KEY `refunded_at_index` (`refunded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: log_idle_incentive_rewards
CREATE TABLE `log_idle_incentive_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `exec_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '探索報酬の受け取り方法',
  `idle_started_at` timestamp NOT NULL COMMENT '放置開始日時',
  `elapsed_minutes` int NOT NULL COMMENT '報酬計算に使用した放置時間(分)',
  `received_reward` json NOT NULL COMMENT '配布報酬情報(変換前情報あり)',
  `received_reward_at` timestamp NOT NULL COMMENT '探索報酬を受け取った日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索報酬報酬ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_items
CREATE TABLE `log_items` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Get: 獲得 Use: 消費',
  `mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_items.id',
  `before_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動前の量',
  `after_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '変動後の量',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報値',
  `trigger_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報オプション',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='アイテム変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_logins
CREATE TABLE `log_logins` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '主キー',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `login_count` int NOT NULL COMMENT 'ログイン回数',
  `is_day_first_login` smallint NOT NULL COMMENT '1日の最初のログインかどうかのフラグ (1: 初ログイン, 0: ログイン2回目以降)',
  `login_day_count` int NOT NULL COMMENT 'ログイン日数',
  `login_continue_day_count` int NOT NULL COMMENT '連続ログイン日数',
  `comeback_day_count` int NOT NULL COMMENT '最終ログインから復帰にかかった日数',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ログインログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_mission_rewards
CREATE TABLE `log_mission_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mission_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションタイプ',
  `received_reward` json NOT NULL COMMENT '配布報酬情報(変換前情報あり)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ミッション報酬ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_outpost_enhancements
CREATE TABLE `log_outpost_enhancements` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_outpost_enhancements.id 強化したゲートの強化項目',
  `before_level` int NOT NULL COMMENT '強化前のレベル',
  `after_level` int NOT NULL COMMENT '強化後のレベル',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ゲート強化変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_pvp_actions
CREATE TABLE `log_pvp_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `sys_pvp_season_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sys_pvp_seasons.id',
  `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストされたPVP関連のAPI',
  `result` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'PVP結果。0: 結果未確定, 1: 敗北, 2: 勝利 3: リタイア 4: 中断復帰キャンセル',
  `my_pvp_status` json NOT NULL COMMENT 'PVPステータス情報',
  `opponent_my_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対戦相手id',
  `opponent_pvp_status` json NOT NULL COMMENT 'PVPステータス情報',
  `in_game_battle_log` json DEFAULT NULL COMMENT 'インゲームのバトルログ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_receive_message_rewards
CREATE TABLE `log_receive_message_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `received_reward` json NOT NULL COMMENT '配布報酬情報',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='メッセージ報酬受取ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_stage_actions
CREATE TABLE `log_stage_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
  `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエストされたステージ関連のAPI',
  `result` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'ステージ結果。0: 結果未確定, 1: 敗北, 2: 勝利',
  `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '使用中のゲート',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '装備中の原画',
  `defeat_enemy_count` int NOT NULL DEFAULT '0' COMMENT '敵撃破数',
  `defeat_boss_enemy_count` int NOT NULL DEFAULT '0' COMMENT 'ボス敵撃破数',
  `score` int NOT NULL DEFAULT '0' COMMENT 'スコア',
  `clear_time_ms` int DEFAULT NULL COMMENT 'クリアタイム(ミリ秒)',
  `discovered_enemies` json NOT NULL COMMENT '発見した敵情報',
  `party_status` json NOT NULL COMMENT 'パーティステータス情報',
  `auto_lap_count` int unsigned NOT NULL COMMENT 'スタミナブースト周回指定',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージ挑戦ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_staminas
CREATE TABLE `log_staminas` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `action_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Get: 獲得 Use: 消費',
  `before_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '変動前の量',
  `after_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '変動後の量',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報値',
  `trigger_option` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '経緯情報オプション',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='スタミナ変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_stores
CREATE TABLE `log_stores` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `seq_no` bigint unsigned DEFAULT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `platform_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム側で定義しているproduct_id',
  `mst_store_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターテーブルのプロダクトID',
  `product_sub_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `product_sub_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実際の販売商品名',
  `raw_receipt` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '復号済み生レシートデータ',
  `raw_price_string` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントから送られてきた単価付き購入価格',
  `currency_code` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `receipt_unique_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ユニークなID',
  `receipt_bundle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
  `os_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `device_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `age` int NOT NULL COMMENT '年齢',
  `paid_amount` bigint NOT NULL COMMENT '有償一次通貨の付与量',
  `free_amount` bigint NOT NULL COMMENT '無償一次通貨の付与量',
  `purchase_price` decimal(20,6) NOT NULL COMMENT 'ストアから送られてきた実際の購入価格',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `trigger_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ショップ購入契機',
  `trigger_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'リクエスト識別IDの種類',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NOT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_index` (`usr_user_id`),
  KEY `opr_product_id_index` (`product_sub_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `user_id_currency_code_purchase_price_index` (`is_sandbox`,`usr_user_id`,`currency_code`,`purchase_price`),
  KEY `created_at_id_index` (`created_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップ購入ログ';

-- Table: log_suspected_users
CREATE TABLE `log_suspected_users` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int unsigned NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `content_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コンテンツのタイプ',
  `target_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '降臨バトルの場合はmst_advent_battles.id',
  `cheat_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_cheat_settings.cheat_type',
  `detail` json DEFAULT NULL COMMENT 'チート判定要因のデータ',
  `suspected_at` timestamp NOT NULL COMMENT 'チート疑いされた日時',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='チート疑惑ユーザーログ';

-- Table: log_system_message_additions
CREATE TABLE `log_system_message_additions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登録経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登録経緯情報値',
  `pre_grant_reward_json` json NOT NULL COMMENT '配布予定の報酬情報',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`),
  KEY `idx_user_id_and_trigger` (`usr_user_id`,`trigger_source`,`trigger_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='システムメッセージ追加ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_trade_shop_items
CREATE TABLE `log_trade_shop_items` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_shop_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_shop_items.id',
  `trade_count` int NOT NULL COMMENT '交換回数',
  `cost_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '消費したリソースのタイプ',
  `cost_amount` int NOT NULL COMMENT '消費したリソースの数',
  `received_reward` json NOT NULL COMMENT '変換後の配布報酬情報(実際にユーザーが受け取った報酬情報)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`),
  KEY `idx_user_id_and_mst_shop_item_id` (`usr_user_id`,`mst_shop_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='トレードショップアイテム変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_tutorial_actions
CREATE TABLE `log_tutorial_actions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `tutorial_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_tutorials.function_name,プレイしたチュートリアル名',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='チュートリアル実行ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_unit_grade_ups
CREATE TABLE `log_unit_grade_ups` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'グレードアップしたユニット(mst_units.id)',
  `before_grade_level` int NOT NULL COMMENT '強化前のグレードレベル',
  `after_grade_level` int NOT NULL COMMENT '強化後のグレードレベル',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニットグレードアップログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_unit_level_ups
CREATE TABLE `log_unit_level_ups` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レベルアップしたユニット(mst_units.id)',
  `before_level` int NOT NULL COMMENT '強化前のレベル',
  `after_level` int NOT NULL COMMENT '強化後のレベル',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニットレベルアップログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_unit_rank_ups
CREATE TABLE `log_unit_rank_ups` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ランクアップしたユニット(mst_units.id)',
  `before_rank` int NOT NULL COMMENT '強化前のランク',
  `after_rank` int NOT NULL COMMENT '強化後のランク',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニットランクアップログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_units
CREATE TABLE `log_units` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_units.id',
  `level` int NOT NULL COMMENT 'レベル',
  `rank` int NOT NULL COMMENT 'ランク',
  `grade_level` int NOT NULL COMMENT 'グレードレベル',
  `trigger_source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報ソース',
  `trigger_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報値1',
  `trigger_value_2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報値2',
  `trigger_value_3` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '経緯情報値3',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`),
  KEY `idx_user_id_and_mst_unit_id` (`usr_user_id`,`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユニット変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_user_levels
CREATE TABLE `log_user_levels` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `before_level` int NOT NULL COMMENT '変更前のレベル',
  `after_level` int NOT NULL COMMENT '変更後のレベル',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `idx_user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーレベル変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;

-- Table: log_user_profiles
CREATE TABLE `log_user_profiles` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でNginxにて生成されるユニークID',
  `request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APIリクエスト単位でクライアントのHTTP Libraryにて生成されるユニークID',
  `logging_no` int NOT NULL COMMENT 'APIリクエスト中でのログの順番',
  `profile_column` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変更したプロフィールの項目',
  `before_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象項目の変更前の値',
  `after_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象項目の変更後の値',
  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
  KEY `user_id_and_created_at` (`usr_user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザープロフィール変動ログ' /*T![ttl] TTL=`created_at` + INTERVAL 31 DAY */ /*T![ttl] TTL_ENABLE='ON' */ /*T![ttl] TTL_JOB_INTERVAL='24h' */;


-- =============================================================================
-- Database: sys (TiDB: local, prefix: sys_)
-- =============================================================================
-- Table: sys_pvp_seasons
CREATE TABLE `sys_pvp_seasons` (
  `id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_at` timestamp NOT NULL COMMENT 'シーズン開始日時',
  `end_at` timestamp NOT NULL COMMENT 'シーズン終了日時',
  `closed_at` timestamp NULL DEFAULT NULL COMMENT 'シーズン終了後のクローズ日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

