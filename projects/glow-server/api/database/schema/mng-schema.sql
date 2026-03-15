DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* 全テーブル分のDROP TABLE文 */
DROP TABLE IF EXISTS `mng_asset_releases`;
DROP TABLE IF EXISTS `mng_master_releases`;
DROP TABLE IF EXISTS `mng_asset_release_versions`;
DROP TABLE IF EXISTS `mng_master_release_versions`;
CREATE TABLE `mng_asset_releases` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `platform` enum('1','2') COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `enabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_asset_release_versions.id',
  `client_compatibility_version` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'クライアント互換性バージョン',
  `description` text COLLATE utf8mb4_bin COMMENT 'メモ欄',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `release_key_platform_unique` (`release_key`,`platform`),
  KEY `platform_enabled_index` (`platform`,`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `mng_master_releases` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `enabled` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'リリース状態',
  `target_release_version_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'opr_master_release_versions.id',
  `client_compatibility_version` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'クライアント互換性バージョン',
  `description` text COLLATE utf8mb4_bin COMMENT 'メモ欄',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `opr_master_releases_release_key_unique` (`release_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `mng_asset_release_versions` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `git_revision` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのリビジョン',
  `git_branch` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ビルドを行なったクライアントリポジトリのカレントブランチ',
  `catalog_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'AddressableAssetをビルドした時のCatalogハッシュ値',
  `platform` enum('1','2') COLLATE utf8mb4_bin NOT NULL COMMENT 'iOS / Androidの識別子',
  `build_client_version` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `asset_total_byte_size` bigint unsigned NOT NULL,
  `catalog_byte_size` bigint unsigned NOT NULL,
  `catalog_file_name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `catalog_hash_file_name` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

CREATE TABLE `mng_master_release_versions` (
  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `release_key` bigint NOT NULL,
  `git_revision` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '適用したGitリビジョン',
  `master_schema_version` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'マスターデータのテーブルスキームのhash化した値',
  `data_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT '全ての実データを一意に識別できるハッシュ値',
  `server_db_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_ja_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_en_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_mst_data_i18n_zh_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_ja_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_en_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `client_opr_data_i18n_zh_hash` varchar(255) COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
