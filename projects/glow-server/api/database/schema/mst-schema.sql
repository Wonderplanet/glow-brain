DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
DROP TABLE IF EXISTS `mst_abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_abilities` (
                                 `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `ability_type` enum('None','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','WindKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma') COLLATE utf8mb4_unicode_ci NOT NULL,
                                 `release_key` bigint NOT NULL DEFAULT '1',
                                 `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_abilities_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_abilities_i18n` (
                                      `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_ability_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                      `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `release_key` bigint NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_api_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_api_actions` (
                                   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `api_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `through_app` tinyint unsigned NOT NULL COMMENT 'アプリバージョンのチェック（強制アプデなど）をスキップするか',
                                   `through_master` tinyint unsigned NOT NULL COMMENT 'マスターデータのバージョンチェックをスキップするか',
                                   `through_date` tinyint unsigned NOT NULL COMMENT '日跨ぎチェックをスキップするか',
                                   `through_asset` tinyint unsigned NOT NULL COMMENT 'アセットデータのバージョンチェックをスキップするか',
                                   `release_key` bigint NOT NULL,
                                   `resource` json DEFAULT NULL,
                                   `created_at` timestamp NULL DEFAULT NULL,
                                   `updated_at` timestamp NULL DEFAULT NULL,
                                   PRIMARY KEY (`id`),
                                   UNIQUE KEY `mst_api_actions_api_path_unique` (`api_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_artwork_fragment_positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_artwork_fragment_positions` (
                                                  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                  `mst_artwork_fragment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
                                                  `position` smallint unsigned DEFAULT NULL COMMENT '表示位置(1~16)',
                                                  `release_key` bigint NOT NULL DEFAULT '1',
                                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけらの表示位置設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_artwork_fragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_artwork_fragments` (
                                         `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
                                         `drop_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ステージのドロップ単位(非ドロップはNULL)',
                                         `drop_percentage` smallint unsigned DEFAULT NULL COMMENT 'ドロップ率(非ドロップはNULL)',
                                         `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `release_key` bigint NOT NULL DEFAULT '1',
                                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_artwork_fragments_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_artwork_fragments_i18n` (
                                              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `mst_artwork_fragment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
                                              `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                              `name` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画のかけら名',
                                              `release_key` bigint NOT NULL DEFAULT '1',
                                              PRIMARY KEY (`id`),
                                              UNIQUE KEY `uk_mst_artwork_fragment_id_language` (`mst_artwork_fragment_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら名などの設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_artworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_artworks` (
                                `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
                                `outpost_additional_hp` bigint unsigned NOT NULL COMMENT '完成時にゲートに加算するHP',
                                `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `sort_order` int unsigned NOT NULL COMMENT 'ソート順',
                                `release_key` bigint NOT NULL DEFAULT '1',
                                PRIMARY KEY (`id`),
                                UNIQUE KEY `mst_artworks_mst_series_id_unique` (`mst_series_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画の設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_artworks_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_artworks_i18n` (
                                     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
                                     `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                     `name` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画名',
                                     `release_key` bigint NOT NULL DEFAULT '1',
                                     PRIMARY KEY (`id`),
                                     UNIQUE KEY `uk_mst_artwork_id_language` (`mst_artwork_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画名などの設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_attack_elements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_attack_elements` (
                                       `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `release_key` bigint NOT NULL DEFAULT '1',
                                       `mst_attack_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `sort_order` int NOT NULL,
                                       `attack_delay` int NOT NULL,
                                       `attack_type` enum('None','Direct','Homing') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `range_start_type` enum('Distance','Koma','KomaLine') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `range_start_parameter` double(8,2) NOT NULL,
                                       `range_end_type` enum('Distance','Koma','KomaLine') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `range_end_parameter` double(8,2) NOT NULL,
                                       `max_target_count` int NOT NULL,
                                       `target` enum('Friend','Foe','Self') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `target_type` enum('All','Character','Outpost') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `target_colors` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `target_roles` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `damage_type` enum('None','Damage','Heal') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `hit_type` enum('Normal','NoNockBack','KnockBack1','KnockBack2','KnockBack3','ForcedKnockBack5') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `probability` int NOT NULL,
                                       `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `power_parameter` int NOT NULL,
                                       `effect_type` enum('None','AttackPowerUp','AttackPowerDown','DamageCut','AttackSpeedDown','MoveSpeedUp','MoveSpeedDown','SlipDamage','Fierce','SpecialAttackSeal','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma') COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `effective_count` int NOT NULL,
                                       `effective_duration` int NOT NULL,
                                       `effect_parameter` int NOT NULL,
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_attacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_attacks` (
                               `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `unit_grade` int NOT NULL,
                               `attack_kind` enum('Normal','Special','Appearance') COLLATE utf8mb4_unicode_ci NOT NULL,
                               `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `killer_colors` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `killer_roles` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `charge_frames` int NOT NULL,
                               `action_frames` int NOT NULL,
                               `attack_delay` int NOT NULL,
                               `next_attack_interval` int NOT NULL,
                               `release_key` int NOT NULL DEFAULT '1',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_attacks_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_attacks_i18n` (
                                    `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `release_key` bigint NOT NULL DEFAULT '1',
                                    `mst_attack_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                    `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `grade_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_avatar_frames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_avatar_frames` (
                                     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `avatar_frame_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `release_key` int NOT NULL DEFAULT '1',
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_avatars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_avatars` (
                               `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `avatar_icon_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `release_key` int NOT NULL DEFAULT '1',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_battle_point_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_battle_point_levels` (
                                           `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `release_key` bigint NOT NULL DEFAULT '1',
                                           `level` int NOT NULL,
                                           `required_level_up_battle_point` int NOT NULL,
                                           `max_battle_point` int NOT NULL,
                                           `charge_amount` int NOT NULL,
                                           `charge_interval` int NOT NULL,
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_configs` (
                               `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `release_key` int NOT NULL DEFAULT '1',
                               `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                               PRIMARY KEY (`id`),
                               UNIQUE KEY `mst_configs_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_emblems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_emblems` (
                               `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `emblem_type` enum('Event','Series') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'エンブレムのタイプ',
                               `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '作品ID',
                               `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                               `release_key` bigint NOT NULL DEFAULT '1',
                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_emblems_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_emblems_i18n` (
                                    `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'エンブレムの名称',
                                    `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'フレーバーテキスト',
                                    `release_key` bigint NOT NULL DEFAULT '1',
                                    PRIMARY KEY (`id`),
                                    KEY `mst_emblem_id_index` (`mst_emblem_id`),
                                    KEY `language_index` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_enemy_characters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_enemy_characters` (
                                        `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `release_key` bigint NOT NULL DEFAULT '1',
                                        `character_unit_kind` enum('Normal','Formidable','Boss') COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `role_type` enum('None','Attack','Balance','Defense','Support','Unique') COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `color` enum('None','Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `sort_order` int NOT NULL,
                                        `hp` int NOT NULL,
                                        `damage_knock_back_count` int NOT NULL,
                                        `move_speed` int NOT NULL,
                                        `well_distance` double(8,2) NOT NULL,
                                        `attack_power` int NOT NULL,
                                        `attack_combo_cycle` int NOT NULL,
                                        `ability1` enum('None','SlipDamageKomaBlock','AttackPowerDownKomaBlock','GustKomaBlock','AttackPowerUpKomaBoost','WindKomaBoost','AttackPowerUpInNormalKoma','MoveSpeedUpInNormalKoma','DamageCutInNormalKoma') COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `ability1_parameter` int NOT NULL,
                                        `bounding_range_front` double(8,2) NOT NULL,
                                        `bounding_range_back` double(8,2) NOT NULL,
                                        `drop_battle_point` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_enemy_characters_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_enemy_characters_i18n` (
                                             `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `release_key` int NOT NULL DEFAULT '1',
                                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_enemy_outposts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_enemy_outposts` (
                                      `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `hp` int NOT NULL,
                                      `release_key` int NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_fragment_box_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_fragment_box_groups` (
                                           `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `mst_fragment_box_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `start_at` timestamp NOT NULL,
                                           `end_at` timestamp NOT NULL,
                                           `release_key` bigint NOT NULL DEFAULT '1',
                                           PRIMARY KEY (`id`),
                                           KEY `mst_fragment_box_group_id_index` (`mst_fragment_box_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_fragment_boxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_fragment_boxes` (
                                      `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_fragment_box_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `release_key` bigint NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`),
                                      UNIQUE KEY `mst_fragment_boxes_mst_item_id_unique` (`mst_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_handout_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_handout_groups` (
                                      `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `release_key` int NOT NULL DEFAULT '1',
                                      `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_handouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_handouts` (
                                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `release_key` int NOT NULL DEFAULT '1',
                                `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                `amount` int NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_idle_incentive_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_idle_incentive_items` (
                                            `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `base_amount` decimal(10,2) NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_idle_incentive_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_idle_incentive_rewards` (
                                              `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `release_key` bigint NOT NULL DEFAULT '1',
                                              `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `base_coin_amount` decimal(10,2) NOT NULL,
                                              `base_exp_amount` decimal(10,2) NOT NULL,
                                              `base_rank_up_material_amount` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT 'リミテッドメモリーのベース獲得量',
                                              `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_idle_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_idle_incentives` (
                                       `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `release_key` bigint NOT NULL DEFAULT '1',
                                       `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `initial_reward_receive_minutes` int unsigned NOT NULL,
                                       `reward_increase_interval_minutes` int unsigned NOT NULL COMMENT '報酬が増加する時間間隔(分)',
                                       `max_idle_hours` int unsigned NOT NULL,
                                       `required_quick_receive_diamond_amount` int unsigned NOT NULL DEFAULT '0',
                                       `max_daily_diamond_quick_receive_amount` int unsigned NOT NULL DEFAULT '0',
                                       `max_daily_ad_quick_receive_amount` int unsigned NOT NULL,
                                       `ad_interval_seconds` int unsigned NOT NULL,
                                       `quick_idle_minutes` int unsigned NOT NULL,
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_items` (
                             `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','Etc') COLLATE utf8mb4_unicode_ci NOT NULL,
                             `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `rarity` enum('N','R','SR','SSR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
                             `sort_order` int NOT NULL DEFAULT '0',
                             `start_date` timestamp NOT NULL,
                             `end_date` timestamp NOT NULL,
                             `release_key` bigint NOT NULL DEFAULT '1',
                             PRIMARY KEY (`id`),
                             KEY `mst_items_item_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_items_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_items_i18n` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `release_key` bigint NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`),
                                  KEY `mst_items_i18n_mst_item_id_language_index` (`mst_item_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_koma_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_koma_lines` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `row` int NOT NULL,
                                  `height` double(8,2) NOT NULL,
                                  `koma_line_layout_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_width` double(8,2) DEFAULT NULL,
                                  `koma1_back_ground_offset` double(8,2) NOT NULL,
                                  `koma1_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust') COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_effect_parameter1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_effect_parameter2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma1_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma2_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma2_width` double(8,2) DEFAULT NULL,
                                  `koma2_back_ground_offset` double(8,2) DEFAULT NULL,
                                  `koma2_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust') COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma2_effect_parameter1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma2_effect_parameter2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma2_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma2_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma2_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma3_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma3_width` double(8,2) DEFAULT NULL,
                                  `koma3_back_ground_offset` double(8,2) DEFAULT NULL,
                                  `koma3_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust') COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma3_effect_parameter1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma3_effect_parameter2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma3_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma3_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma3_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma4_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma4_width` double(8,2) DEFAULT NULL,
                                  `koma4_back_ground_offset` double(8,2) DEFAULT NULL,
                                  `koma4_effect_type` enum('None','AttackPowerUp','AttackPowerDown','MoveSpeedUp','SlipDamage','Tailwind','Headwind','Fierce','SpecialAttackSeal','Gust') COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma4_effect_parameter1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma4_effect_parameter2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `koma4_effect_target_side` enum('All','Player','Enemy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma4_effect_target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  `koma4_effect_target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_achievement_dependencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_achievement_dependencies` (
                                                        `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                        `release_key` int NOT NULL DEFAULT '1',
                                                        `group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '依存関係のグルーピングID',
                                                        `mst_mission_achievement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
                                                        `unlock_order` int unsigned NOT NULL COMMENT '対象グループ内でのミッションの開放順。1つ前のunlock_orderを持つミッションをクリアしたら開放される。',
                                                        PRIMARY KEY (`id`),
                                                        UNIQUE KEY `uk_group_id_mst_mission_achievement_id` (`group_id`,`mst_mission_achievement_id`),
                                                        UNIQUE KEY `uk_group_id_unlock_order` (`group_id`,`unlock_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_achievements` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` int NOT NULL DEFAULT '1',
                                            `criterion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
                                            `criterion_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
                                            `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
                                            `unlock_criterion_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '開放条件タイプ',
                                            `unlock_criterion_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '開放条件値',
                                            `unlock_criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '開放条件の達成回数',
                                            `group_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー。mission_full_completeのカウント対象となる。',
                                            `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_rewards.group_id',
                                            `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '並び順',
                                            `destination_scene` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_achievements_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_achievements_i18n` (
                                                 `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `mst_mission_achievement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
                                                 `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                                 `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
                                                 `release_key` bigint NOT NULL DEFAULT '1',
                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_beginners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_beginners` (
                                         `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `release_key` bigint NOT NULL DEFAULT '1',
                                         `criterion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
                                         `criterion_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
                                         `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
                                         `unlock_day` smallint unsigned NOT NULL DEFAULT '0' COMMENT '開始からの開放日',
                                         `group_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
                                         `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
                                         `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
                                         `sort_order` int unsigned NOT NULL COMMENT '並び順',
                                         `destination_scene` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
                                         PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_beginners_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_beginners_i18n` (
                                              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `mst_mission_achievement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
                                              `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                              `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ダイアログタイトル',
                                              `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '初心者ミッションテキスト',
                                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_dailies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_dailies` (
                                       `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `release_key` bigint NOT NULL DEFAULT '1',
                                       `criterion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
                                       `criterion_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
                                       `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
                                       `group_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
                                       `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
                                       `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
                                       `sort_order` int unsigned NOT NULL COMMENT '並び順',
                                       `destination_scene` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_dailies_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_dailies_i18n` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `mst_mission_daily_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_dailies.id',
                                            `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                            `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_daily_bonuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_daily_bonuses` (
                                             `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `release_key` bigint NOT NULL DEFAULT '1',
                                             `mission_daily_bonus_type` enum('Total','DailyBonus') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'デイリーボーナスタイプ',
                                             `login_day_count` int unsigned NOT NULL COMMENT '条件とするログイン日数',
                                             `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.id',
                                             `sort_order` int unsigned NOT NULL DEFAULT '0' COMMENT '表示順',
                                             PRIMARY KEY (`id`),
                                             UNIQUE KEY `uk_type_login_day_count` (`mission_daily_bonus_type`,`login_day_count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_rewards` (
                                       `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `release_key` bigint NOT NULL DEFAULT '1',
                                       `group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬グルーピングID',
                                       `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
                                       `resource_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬リソースID',
                                       `resource_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '報酬の個数',
                                       `sort_order` int unsigned NOT NULL COMMENT '並び順',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_weeklies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_weeklies` (
                                        `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `release_key` bigint NOT NULL DEFAULT '1',
                                        `criterion_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '達成条件タイプ',
                                        `criterion_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '達成条件値',
                                        `criterion_count` bigint unsigned NOT NULL DEFAULT '0' COMMENT '達成回数',
                                        `group_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '分類キー',
                                        `bonus_point` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ミッションボーナスポイント量',
                                        `mst_mission_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_reward_groups.group_id',
                                        `sort_order` int unsigned NOT NULL COMMENT '並び順',
                                        `destination_scene` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションから遷移する画面',
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_mission_weeklies_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_mission_weeklies_i18n` (
                                             `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                             `mst_mission_weekly_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_weeklies.id',
                                             `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                             `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '説明',
                                             `release_key` bigint NOT NULL DEFAULT '1',
                                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_ng_words`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_ng_words` (
                                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `word` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `release_key` int NOT NULL DEFAULT '1',
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_outpost_enhancement_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_outpost_enhancement_levels` (
                                                  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                  `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                  `level` int unsigned NOT NULL,
                                                  `cost_coin` int unsigned NOT NULL,
                                                  `enhancement_value` double(8,2) NOT NULL,
                                                  `release_key` bigint NOT NULL DEFAULT '1',
                                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_outpost_enhancement_levels_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_outpost_enhancement_levels_i18n` (
                                                       `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                       `mst_outpost_enhancement_level_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                       `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                       `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                       `release_key` bigint NOT NULL DEFAULT '1',
                                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_outpost_enhancements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_outpost_enhancements` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `outpost_enhancement_type` enum('BeamDamage','BeamInterval','LeaderPointSpeed','LeaderPointLimit','OutpostHp','SummonInterval','LeaderPointUp') COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_outpost_enhancements_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_outpost_enhancements_i18n` (
                                                 `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `release_key` bigint NOT NULL DEFAULT '1',
                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_outposts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_outposts` (
                                `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                `start_at` timestamp NOT NULL,
                                `end_at` timestamp NOT NULL,
                                `release_key` bigint NOT NULL DEFAULT '1',
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_pack_contents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_pack_contents` (
                                     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `mst_pack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
                                     `resource_type` enum('FreeDiamond','Coin','Item') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                     `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '内包物のID(resource_typeがitemの場合のみ)',
                                     `resource_amount` bigint unsigned NOT NULL DEFAULT '0' COMMENT '内包物の数量',
                                     `is_bonus` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'おまけフラグ',
                                     `display_order` int unsigned NOT NULL COMMENT '表示順序',
                                     `release_key` int NOT NULL DEFAULT '1',
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_packs` (
                             `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_products.id',
                             `discount_rate` smallint unsigned NOT NULL COMMENT '割引率',
                             `sale_condition` enum('StageClear','UserLevel') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '販売開始条件',
                             `sale_condition_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '販売開始条件値',
                             `sale_hours` smallint unsigned DEFAULT NULL COMMENT '条件達成からの販売時間',
                             `cost_type` enum('Cash','PaidDiamond') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '販売開始条件',
                             `cost_amount` int unsigned NOT NULL COMMENT 'コスト量',
                             `is_recommend` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'おすすめフラグ',
                             `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'バナー画像パス',
                             `pack_decoration` enum('Gold') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'パックの装飾',
                             `release_key` int NOT NULL DEFAULT '1',
                             PRIMARY KEY (`id`),
                             KEY `mst_packs_sale_condition_index` (`sale_condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_packs_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_packs_i18n` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `mst_pack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
                                  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パック名',
                                  `release_key` int NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_pages` (
                             `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                             `release_key` int NOT NULL DEFAULT '1',
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_quests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_quests` (
                              `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `quest_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `sort_order` int NOT NULL DEFAULT '0',
                              `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `start_date` timestamp NOT NULL,
                              `end_date` timestamp NOT NULL,
                              `release_key` bigint NOT NULL DEFAULT '1',
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_quests_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_quests_i18n` (
                                   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `flavor_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `release_key` int NOT NULL DEFAULT '1',
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_release_keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_release_keys` (
                                    `release_key` int NOT NULL,
                                    `start_at` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
                                    `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                    PRIMARY KEY (`release_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_series`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_series` (
                              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作品ID',
                              `jump_plus_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ジャンプ+作品へのURL',
                              `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `banner_asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'バナーのアセット',
                              `release_key` bigint NOT NULL DEFAULT '1',
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_series_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_series_i18n` (
                                   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
                                   `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
                                   `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作品名',
                                   `prefix_word` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '絞込も文字(ア行など)',
                                   `release_key` bigint NOT NULL DEFAULT '1',
                                   PRIMARY KEY (`id`),
                                   UNIQUE KEY `uk_mst_series_id_language` (`mst_series_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='作品の言語設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_shop_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_shop_items` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
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
                                  `release_key` int NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーに販売する非課金商品を管理する';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_special_attacks_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_special_attacks_i18n` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                            `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_speech_balloons_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_speech_balloons_i18n` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `language` enum('ja') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                            `balloon_type` enum('Maru','Fuwa','Toge') COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_drop_additions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_drop_additions` (
                                            `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` int NOT NULL DEFAULT '1',
                                            `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `wave_interval` int unsigned NOT NULL DEFAULT '0',
                                            `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_drop_bases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_drop_bases` (
                                        `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `release_key` int NOT NULL DEFAULT '1',
                                        `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `exp` int unsigned NOT NULL DEFAULT '0',
                                        `coin` int unsigned NOT NULL DEFAULT '0',
                                        `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                        `mst_item_amount` int unsigned NOT NULL DEFAULT '0',
                                        PRIMARY KEY (`id`),
                                        UNIQUE KEY `mst_stage_drop_bases_mst_stage_id_unique` (`mst_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_limit_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_limit_statuses` (
                                            `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` bigint NOT NULL DEFAULT '1',
                                            `only_rarity` enum('SSR','SR','R','N') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                            `over_rarity` enum('SSR','SR','R','N') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                            `over_summon_cost` int DEFAULT NULL,
                                            `under_summon_cost` int DEFAULT NULL,
                                            `mst_series_ids` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_reward_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_reward_groups` (
                                           `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `mst_stage_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `reward_category` enum('Always','FirstClear') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
                                           `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                           `resource_amount` int unsigned NOT NULL,
                                           `weight` int unsigned NOT NULL,
                                           `release_key` bigint unsigned NOT NULL DEFAULT '1',
                                           PRIMARY KEY (`id`),
                                           KEY `stage_reward_group_index` (`mst_stage_reward_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_tips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_tips` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `release_key` bigint unsigned NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stage_treasures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stage_treasures` (
                                       `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `release_key` int NOT NULL DEFAULT '1',
                                       `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `wave` int NOT NULL,
                                       `mst_handout_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       PRIMARY KEY (`id`),
                                       UNIQUE KEY `uk_mst_stage_id_and_wave` (`mst_stage_id`,`wave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stages` (
                              `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `mst_quest_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                              `stage_number` int NOT NULL DEFAULT '0',
                              `cost_stamina` int unsigned NOT NULL,
                              `exp` int unsigned NOT NULL,
                              `coin` int unsigned NOT NULL,
                              `mst_stage_reward_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `reward_amount` int unsigned NOT NULL,
                              `mst_artwork_fragment_drop_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artwork_fragments.drop_group_id',
                              `prev_mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `mst_stage_tips_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `sort_order` int unsigned NOT NULL,
                              `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `bgm_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `mst_page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `mst_enemy_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `boss_mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `normal_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `normal_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `normal_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `boss_enemy_hp_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `boss_enemy_attack_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `boss_enemy_speed_coef` decimal(10,2) NOT NULL DEFAULT '1.00',
                              `enemy_sequence_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                              `mst_stage_limit_status_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                              `release_key` bigint unsigned NOT NULL DEFAULT '1',
                              PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_stages_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_stages_i18n` (
                                   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                   `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `result_tips` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `release_key` int NOT NULL DEFAULT '1',
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_store_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_store_products` (
                                      `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `release_key` bigint NOT NULL COMMENT '変更が適用されるリリースキー',
                                      `product_id_ios` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStoreのプロダクトID',
                                      `product_id_android` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'GooglePlayのプロダクトID',
                                      PRIMARY KEY (`id`),
                                      KEY `product_id_ios_index` (`product_id_ios`),
                                      KEY `product_id_android_index` (`product_id_android`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='プラットフォーム（AppStore, GooglePlay）に登録している商品のIDを管理する\nリストアがあるため一度定義したら変えない';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_store_products_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_store_products_i18n` (
                                           `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `price_ios` decimal(10,3) NOT NULL COMMENT 'AppStoreの価格',
                                           `price_android` decimal(10,3) NOT NULL COMMENT 'GooglePlayの価格',
                                           `release_key` int NOT NULL DEFAULT '1',
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ストア商品情報の多言語を管理する';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_trade_pieces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_trade_pieces` (
                                    `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `trade_required_amount` int NOT NULL,
                                    `trade_amount` int NOT NULL,
                                    `release_key` int NOT NULL DEFAULT '1',
                                    PRIMARY KEY (`id`),
                                    KEY `mst_trade_pieces_mst_unit_id_index` (`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_abilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_abilities` (
                                      `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_ability_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `ability_parameter` int NOT NULL,
                                      `release_key` bigint NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_encyclopedia_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_encyclopedia_rewards` (
                                                 `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                                 `unit_encyclopedia_rank` int unsigned NOT NULL COMMENT '図鑑ランク(グレードの合算値)',
                                                 `resource_type` enum('Exp','Coin','FreeDiamond','Item','Emblem') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬タイプ',
                                                 `resource_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '報酬リソースID',
                                                 `resource_amount` int unsigned NOT NULL DEFAULT '0' COMMENT '報酬の個数',
                                                 `release_key` bigint NOT NULL DEFAULT '1',
                                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='キャラ図鑑報酬の設定';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_exchanges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_exchanges` (
                                      `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `required_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `required_amount` int NOT NULL,
                                      `required_diamond_amount` int NOT NULL,
                                      `release_key` int NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_grade_coefficients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_grade_coefficients` (
                                               `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `grade_level` int unsigned NOT NULL,
                                               `coefficient` int unsigned NOT NULL,
                                               `release_key` bigint NOT NULL DEFAULT '1',
                                               PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_grade_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_grade_ups` (
                                      `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `unit_label` enum('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DropN',
                                      `grade_level` int unsigned NOT NULL,
                                      `require_amount` int unsigned NOT NULL COMMENT 'グレードアップに必要なかけら数',
                                      `release_key` bigint NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`),
                                      UNIQUE KEY `uk_unit_label_grade_level` (`unit_label`,`grade_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_level_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_level_ups` (
                                      `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                      `unit_label` enum('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DropN',
                                      `level` int NOT NULL,
                                      `required_coin` int NOT NULL,
                                      `release_key` int NOT NULL DEFAULT '1',
                                      PRIMARY KEY (`id`),
                                      UNIQUE KEY `uk_unit_label_level` (`unit_label`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_unit_rank_ups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_unit_rank_ups` (
                                     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `unit_label` enum('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `rank` int NOT NULL,
                                     `amount` int NOT NULL,
                                     `require_level` int NOT NULL,
                                     `release_key` int NOT NULL DEFAULT '1',
                                     PRIMARY KEY (`id`),
                                     UNIQUE KEY `uk_unit_label_rank` (`unit_label`,`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_units` (
                             `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `fragment_mst_item_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `role_type` enum('Attack','Balance','Defense','Support','Unique') COLLATE utf8mb4_unicode_ci NOT NULL,
                             `attack_range_type` enum('Short','Middle','Long') COLLATE utf8mb4_unicode_ci NOT NULL,
                             `unit_label` enum('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
                             `image_id` bigint unsigned NOT NULL,
                             `asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                             `rarity` enum('N','R','SR','SSR','UR') COLLATE utf8mb4_unicode_ci NOT NULL,
                             `sort_order` int unsigned NOT NULL,
                             `summon_cost` int unsigned NOT NULL,
                             `summon_cool_time` int unsigned NOT NULL,
                             `min_hp` int unsigned NOT NULL,
                             `max_hp` int unsigned NOT NULL,
                             `damage_knock_back_count` int unsigned NOT NULL,
                             `move_speed` int unsigned NOT NULL,
                             `well_distance` double(8,2) NOT NULL,
                             `min_attack_power` int unsigned NOT NULL,
                             `max_attack_power` int unsigned NOT NULL,
                             `attack_combo_cycle` int unsigned NOT NULL,
                             `mst_unit_ability_id1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `bounding_range_front` double(8,2) NOT NULL,
                             `bounding_range_back` double(8,2) NOT NULL,
                             `series_asset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                             `release_key` int NOT NULL DEFAULT '1',
                             PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_units_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_units_i18n` (
                                  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                  `release_key` int NOT NULL DEFAULT '1',
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_user_level_bonus_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user_level_bonus_groups` (
                                               `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `mst_user_level_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `resource_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `resource_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `resource_amount` int NOT NULL,
                                               `release_key` bigint NOT NULL DEFAULT '1',
                                               PRIMARY KEY (`id`),
                                               KEY `level_bonus_group_index` (`mst_user_level_bonus_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_user_level_bonuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user_level_bonuses` (
                                          `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `level` int NOT NULL,
                                          `mst_user_level_bonus_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `release_key` bigint NOT NULL DEFAULT '1',
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `mst_user_level_bonuses_level_unique` (`level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mst_user_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mst_user_levels` (
                                   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `level` int NOT NULL,
                                   `stamina` int NOT NULL,
                                   `exp` bigint NOT NULL,
                                   `release_key` bigint NOT NULL DEFAULT '1',
                                   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `opr_asset_release_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_asset_release_controls` (
                                              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                                              `version` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                              `platform` int DEFAULT '0',
                                              `branch` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ブランチ名',
                                              `hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コミットハッシュ',
                                              `version_no` int NOT NULL DEFAULT '0',
                                              `release_at` timestamp NOT NULL COMMENT 'リリース予定日時',
                                              `release_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リリース内容のメモ',
                                              `created_at` timestamp NULL DEFAULT NULL,
                                              `updated_at` timestamp NULL DEFAULT NULL,
                                              PRIMARY KEY (`id`),
                                              KEY `idx_release_at` (`release_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_client_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_client_versions` (
                                       `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `client_version` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                       `platform` int NOT NULL,
                                       `is_force_update` tinyint NOT NULL,
                                       `created_at` timestamp NULL DEFAULT NULL,
                                       `updated_at` timestamp NULL DEFAULT NULL,
                                       `release_key` int NOT NULL DEFAULT '1',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_coin_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_coin_products` (
                                     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `release_key` int NOT NULL DEFAULT '1',
                                     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `coin_amount` int NOT NULL,
                                     `required_diamond_amount` int NOT NULL,
                                     `is_free` tinyint NOT NULL DEFAULT '0',
                                     `start_at` timestamp NULL DEFAULT NULL,
                                     `end_at` timestamp NULL DEFAULT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_normal_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_normal_prizes` (
                                           `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `release_key` int NOT NULL DEFAULT '1',
                                           `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `weight` int NOT NULL,
                                           PRIMARY KEY (`id`),
                                           UNIQUE KEY `uk_opr_gacha_normal_id_mst_unit_id` (`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_normals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_normals` (
                                     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `release_key` int NOT NULL DEFAULT '1',
                                     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `required_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `required_item_amount` int NOT NULL DEFAULT '1',
                                     `required_diamond_amount` int NOT NULL DEFAULT '1',
                                     `prize_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                     `start_at` timestamp NULL DEFAULT NULL,
                                     `end_at` timestamp NULL DEFAULT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_normals_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_normals_i18n` (
                                          `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `release_key` int NOT NULL DEFAULT '1',
                                          `opr_gacha_normal_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                          `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `uk_opr_gacha_normal_id_language` (`opr_gacha_normal_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_super_prizes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_super_prizes` (
                                          `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `release_key` int NOT NULL DEFAULT '1',
                                          `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                          `weight` int unsigned NOT NULL,
                                          `start_at` timestamp NULL DEFAULT NULL,
                                          `end_at` timestamp NULL DEFAULT NULL,
                                          PRIMARY KEY (`id`),
                                          UNIQUE KEY `opr_gacha_super_prizes_group_id_mst_unit_id_unique` (`group_id`,`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_supers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_supers` (
                                    `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `release_key` int NOT NULL DEFAULT '1',
                                    `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `multi_draw_count` int unsigned NOT NULL DEFAULT '1',
                                    `single_required_diamond_amount` int unsigned NOT NULL DEFAULT '1',
                                    `multi_required_diamond_amount` int unsigned NOT NULL DEFAULT '1',
                                    `wish_count` int unsigned NOT NULL DEFAULT '1',
                                    `wish_rate` double(5,2) unsigned NOT NULL,
                                    `prize_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                    `start_at` timestamp NULL DEFAULT NULL,
                                    `end_at` timestamp NULL DEFAULT NULL,
                                    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_gacha_supers_i18n`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_gacha_supers_i18n` (
                                         `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `release_key` int NOT NULL DEFAULT '1',
                                         `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `opr_gacha_super_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `language` enum('ja','en','zh-Hant') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ja',
                                         `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                         `start_at` timestamp NULL DEFAULT NULL,
                                         `end_at` timestamp NULL DEFAULT NULL,
                                         PRIMARY KEY (`id`),
                                         UNIQUE KEY `opr_gacha_supers_i18n_opr_gacha_super_id_language_unique` (`opr_gacha_super_id`,`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_master_release_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_master_release_controls` (
                                               `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `release_key` bigint NOT NULL,
                                               `git_revision` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターデータのコミットハッシュ',
                                               `release_at` timestamp NOT NULL COMMENT 'リリース予定日時',
                                               `release_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'リリース内容のメモ',
                                               `client_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `zh-Hant_client_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `en_client_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `ja_client_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `client_opr_data_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                               `zh-Hant_client_opr_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `en_client_opr_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `ja_client_opr_i18n_data_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                               `created_at` timestamp NULL DEFAULT NULL,
                                               `updated_at` timestamp NULL DEFAULT NULL,
                                               PRIMARY KEY (`id`),
                                               UNIQUE KEY `git_revision_release_key_client_data_hash_unique` (`git_revision`,`release_key`,`client_data_hash`),
                                               KEY `idx_release_at` (`release_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_products` (
                                `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                `product_type` enum('diamond','pack') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '商品タイプ',
                                `purchasable_count` int unsigned DEFAULT NULL COMMENT '購入可能回数',
                                `paid_amount` bigint NOT NULL DEFAULT '0' COMMENT '配布する有償一次通貨',
                                `display_priority` int unsigned NOT NULL COMMENT '表示優先度',
                                `start_date` timestamp NOT NULL COMMENT '販売開始日時',
                                `end_date` timestamp NOT NULL COMMENT '販売終了日時',
                                `release_key` int NOT NULL DEFAULT '1',
                                PRIMARY KEY (`id`),
                                KEY `mst_store_product_id_index` (`mst_store_product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーに販売する実際の商品を管理する\n1mst_store_productに対して複数';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `opr_stage_drop_additions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `opr_stage_drop_additions` (
                                            `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `release_key` int NOT NULL DEFAULT '1',
                                            `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `wave_interval` int unsigned NOT NULL DEFAULT '0',
                                            `mst_handout_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                                            `start_at` timestamp NOT NULL,
                                            `end_at` timestamp NOT NULL,
                                            PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2014_10_12_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2022_10_19_152400_create_user_devices_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2022_11_07_094933_create_device_link_passwords_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2022_11_21_023851_create_device_link_socials_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2023_01_01_000000_alter_users_table_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2023_01_01_000001_alter_users_table_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2023_03_31_000000_create_opr_master_release_control',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2023_09_05_033238_wp_currency_mst_create_currency_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2023_09_05_033240_wp_currency_usr_create_currency_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2023_09_27_053432_wp_currency_add_product_id_to_allowance',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2023_09_29_103126_wp_currency_add_unique_key_user_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2023_10_12_074344_wp_currency_add_column_to_usr_store_product_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2023_10_16_075119_add_column_to_usr_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2023_10_16_083447_create_opr_tos_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2023_10_17_083447_create_opr_privacy_policy_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2023_10_18_075119_create_usr_user_parameters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2023_10_18_075120_create_usr_user_profiles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2023_10_18_075121_create_usr_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2023_10_18_075122_create_usr_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2023_10_18_075123_create_usr_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2023_10_18_075124_create_usr_stage_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2023_10_19_075124_create_opr_client_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2023_10_20_055758_wp_currency_rename_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2023_10_23_015040_create_mst_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2023_10_23_022705_create_mst_units_i18n_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2023_10_23_023110_create_mst_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2023_10_23_023314_create_mst_items_i18n_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2023_10_23_024309_create_mst_unit_awakes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2023_10_23_031457_wp_currecy_change_platform_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2023_10_23_102214_wp_currency_rename_opr_product_id_to_product_sub_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2023_10_24_095750_wp_currency_add_raw_price_string_to_log_stores',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2023_10_30_063902_wp_currency_add_device_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2023_10_30_145256_create_mst_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2023_10_31_064056_create_mst_release_keys_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2023_11_02_030404_wp_currency_add_is_sandbox',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2023_11_02_033931_create_mst_unit_level_ups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2023_11_06_033931_alter_all_table_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2023_11_07_015911_wp_currency_add_soft_delete',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2023_11_07_033932_alter_all_table_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2023_11_07_060718_add_icon_path_to_mst_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2023_11_07_064014_rename_require_diamond_amount_to_mst_unit_awakes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2023_11_08_033931_alter_all_table_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2023_11_08_082543_wp_currency_change_column_comments',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2023_11_09_055711_create_mst_unit_rank_ups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2023_11_13_041349_add_piece_id_to_mst_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2023_11_13_130353_drop_opr_policy_versions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2023_11_17_063533_add_require_level_to_mst_unit_rank_ups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2023_11_20_080817_create_mst_unit_exchanges_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2023_11_21_060945_add_unique_index_to_usr_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2023_11_22_032210_wp_currency_add_trigger_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2023_11_22_054216_alter_and_drop_mst_stage_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2023_11_25_041129_alter_usr_user_profiles_add_my_id',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2023_11_28_054216_create_normal_gacha_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2023_11_30_032812_create_mst_ng_words_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2023_11_30_054216_add_unique_index_to_usr_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2023_12_01_000000_create_usr_gacha_normals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2023_12_01_083652_add_avatar_icon_path_to_mst_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2023_12_03_054216_add_unique_index_to_usr_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2023_12_04_063533_add_buy_stamina_columns_to_usr_user_parameters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2023_12_04_085910_wp_currency_add_currency_revert_history_log',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2023_12_05_052249_create_mst_trade_pieces_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2023_12_07_063533_add_treasure_received_wave_to_usr_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2023_12_07_063534_create_mst_stage_treasures_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2023_12_07_063535_create_mst_handouts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2023_12_07_072204_create_mst_avatars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2023_12_08_010312_create_mst_avatar_frames_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2023_12_08_035022_create_usr_avatars_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2023_12_08_102746_create_usr_avatar_frames_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2023_12_12_111127_alter_opr_master_release_controls_add_client_hash',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2023_12_13_102746_create_opr_coin_products_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2023_12_13_102747_add_buy_coin_columns_to_usr_user_parameters_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2023_12_14_054143_create_mst_configs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2023_12_15_101427_alter_mst_items_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2023_12_15_101505_alter_mst_items_i18n_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2023_12_18_110836_alter_column_mst_configs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2023_12_18_113421_create_usr_user_buy_counts_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2023_12_19_034848_alter_mst_units_add_asset_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2023_12_19_051052_wp_currency_change_user_id_name',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2023_12_19_064126_wp_currency_add_age_to_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2023_12_21_060919_create_opr_gacha_supers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2023_12_21_060920_create_opr_gacha_supers_i18n_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2023_12_21_060921_create_usr_gacha_supers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2023_12_21_062038_create_opr_gacha_super_prizes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2023_12_25_035738_mst_user_level',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'2023_12_25_053301_change_usr_player_parameteres',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2023_12_25_064745_wp_currency_change_log_seq_no_null',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'2023_12_25_083944_add_prize_group_id_column_to_opr_gacha_normals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'2023_12_25_084206_add_group_id_column_to_opr_gacha_normal_prizes_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'2023_12_26_102430_create_mst_stage_drop_bases_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2023_12_26_102431_create_mst_stage_drop_additions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2023_12_26_111731_create_opr_stage_drop_additions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2024_01_04_031154_wp_currency_add_vip_ppoint',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2024_01_09_060344_alter_mst_stages_create_mst_stages_i18n',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2024_01_11_010744_remove_avatar_icon_path_to_mst_units_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2024_01_11_083219_change_language_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2024_01_15_124004_add_columns_to_opr_products',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2024_01_16_020040_add_usr_store_products',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2024_01_18_071911_change_mst_bonus_column',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2024_01_18_092329_wp_currency_add_id_to_usr_store_product_history',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2024_01_18_112753_create_quest_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2024_01_20_075907_create_usr_stages',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2024_01_29_025749_add_client_opr_data_hash_to_opr_master_release_controls_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2024_01_29_033757_create_mst_pack_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2024_01_29_035345_create_usr_condition_packs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2024_01_31_093719_remove_elapse_days_from_mst_packs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2024_02_01_034800_add_primary_mst_unit_rank_ups_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2024_02_01_125512_add_resource_amount_to_mst_pack_contents',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2024_02_02_124303_modify_pack',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2024_02_05_035450_create_mst_idle_incentives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2024_02_05_075831_add_sort_order_to_mst_stages_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2024_02_06_052850_create_usr_idle_incentives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2024_02_06_091628_add_idle_coin_to_mst_shop_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2024_02_07_061606_adjust_cost_type_in_mst_shop_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2024_02_07_101526_add_column_mst_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2024_02_07_101913_add_stage_table_for_client',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2024_02_08_113517_remove_diamond_from_user_parameter',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2024_02_09_053320_add_columns_to_usr_idle_incentives',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2024_02_09_064009_create_mst_treasure_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2024_02_14_110624_add_effect_value_to_mst_items',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2024_02_16_073429_change_resource_type_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2024_02_19_063354_add_column_to_mst_idle_incentives_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2024_02_20_033603_add_pack_decoration_to_mst_packs',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2024_02_22_084648_add_base_rank_up_material_amount_to_mst_idle_incentive_rewards',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2024_02_29_081939_add_unique_idx_to_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2024_03_04_105113_create_usr_parties',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2024_03_05_040015_add_column_mst_stages_and_enemy',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2024_03_05_084408_add_release_key',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2024_03_06_024654_drop_created_at_and_updated_at_from_mst',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2024_03_06_092048_add_attack_range_type_to_mst_enemy_characters',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2024_03_07_111541_cleaning_migration',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2024_02_09_061343_create_opr_asset_release_controls_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2024_02_15_092211_create_master_api_actions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2024_02_19_084639_wp_currency_create_index_created_at_to_log_currency_revert_history_paid_free',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2024_02_19_093328_wp_currency_create_index_to_log_store',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2024_03_01_110059_add_column_to_opr_master_release_controls_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2024_03_07_033925_create_mission_normal_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2024_03_12_064625_create_mst_outposts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2024_03_12_064630_create_usr_outposts_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2024_03_14_033627_add_some_columns_to_mst_units',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2024_03_15_061911_alter_columns_in_mst_unit_rank_ups',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2024_03_18_020854_change_schema',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2024_03_22_012049_alter_columns_for_ability',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2024_03_22_035350_wp_currency_create_index_cteated_at_id_log_stores',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2024_03_22_060528_create_mst_unit_grade_ups',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2024_03_22_061840_rename_column_awake_count_to_grade_level_in_usr_units',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (146,'2024_03_27_085358_add_unique_index_to_usr_outpost_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (147,'2024_03_27_094113_create_mission_dailies_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (148,'2024_03_29_072802_drop_unnecessasry_column_in_usr_user_buy_counts',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (149,'2024_03_29_082351_create_mst_mission_rewards_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (150,'2024_04_02_113251_wp_currency_change_mst_product_id_varchar_255',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (151,'2024_04_03_064712_create_usr_mission_recent_additions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (152,'2024_04_04_012608_wp_currency_add_column_nginx_request_id_to_log_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (153,'2024_04_05_110624_create_mst_fragment_boxes',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (154,'2024_04_10_063530_alter_columns_mst_mission_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (155,'2024_04_10_113317_create_usr_user_logins_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (156,'2024_04_12_074627_create_mst_unit_grade_coefficient',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (157,'2024_04_12_115431_add_column_party_no_to_usr_stage_sessions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (158,'2024_04_15_055346_create_mst_mission_weeklies_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (159,'2024_04_16_054832_alter_add_continue_usr_stage_sessions',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (160,'2024_04_16_070405_create_mst_mission_i18n_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (161,'2024_04_17_044436_create_mst_mission_daily_bonuses_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (162,'2024_04_22_105833_alter_mst_idle_incentives_add_diamond',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (163,'2024_04_22_111447_alter_usr_idle_incentives_add_diamond',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (164,'2024_04_25_072014_alter_usr_user_profiles_add_avatar_emblem',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (165,'2024_04_26_062049_create_mst_emblems_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (166,'2024_04_26_062252_create_mst_emblems_i18n_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (167,'2024_04_26_062314_create_mst_series_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (168,'2024_04_26_062354_create_usr_emblems_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (169,'2024_04_26_070226_add_column_to_usr_user_logins_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (170,'2024_04_26_135853_alter_mst_mission_rewards_change_resource_type',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (171,'2024_04_26_135854_alter_mst_stage_reward_groups_change_resource_type',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (172,'2024_05_02_082040_drop_required_mst_item_id_from_mst_unit_rank_ups',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (173,'2024_05_10_112329_add_unique_index_to_unit_enhance_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (174,'2024_05_11_022911_create_mst_mission_beginners',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (175,'2024_05_11_022955_create_mst_mission_beginners_i18n',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (176,'2024_05_11_023109_create_usr_mission_beginners',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (177,'2024_05_11_023154_create_usr_mission_beginner_progresses',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (178,'2024_05_11_023246_create_usr_mission_statuses',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (179,'2024_05_11_112329_add_client_schemas',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (180,'2024_05_13_103835_add_mst_encyclopedia_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (181,'2024_05_13_111755_add_usr_encyclopedia_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (182,'2024_05_14_060300_alter_coin_column_usr_user_parameter_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (183,'2024_05_14_102216_wp_currency_add_seq_no_to_log_currency_free',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (184,'2024_05_15_073601_unit_ability_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (185,'2024_05_22_034858_alter_drop_rate_to_drop_percentage_in_mst_artwork_fragments',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (186,'2024_05_22_114406_drop_treasure_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (187,'2024_05_23_050608_alter_usr_items_amount_column',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (188,'2024_05_27_023726_wp_currency_remove_cash',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (189,'2024_05_28_050339_create_mst_attack_elements',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (190,'2024_05_29_051535_alter_table_mst_koma_lines',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (191,'2024_05_29_085257_alter_table_for_data_import',2);
