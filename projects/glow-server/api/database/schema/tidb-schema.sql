/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `log_allowances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_allowances` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ストアのプロダクトID',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_product_id',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay のどちらで購入フローをしようとしているか',
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'alllowanceの変更契機',
  `trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象のallowanceのID',
  `trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='購入許可ログ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_currency_frees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_currency_frees` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logging_no` bigint unsigned NOT NULL COMMENT 'ログ登録番号',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `before_ingame_amount` bigint NOT NULL COMMENT '変更前のゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数',
  `before_bonus_amount` bigint NOT NULL COMMENT '変更前のショップ販売の追加ボーナスで取得した無償一次通貨の数',
  `before_reward_amount` bigint NOT NULL COMMENT '変更前の広告視聴等の報酬で取得した無償一次通貨の数',
  `change_ingame_amount` bigint NOT NULL COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数\n消費の場合は負',
  `change_bonus_amount` bigint NOT NULL COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の数\n消費の場合は負',
  `change_reward_amount` bigint NOT NULL COMMENT '広告視聴等の報酬で取得した無償一次通貨の数\n消費の場合は負',
  `current_ingame_amount` bigint NOT NULL COMMENT 'ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の現在の数',
  `current_bonus_amount` bigint NOT NULL COMMENT 'ショップ販売の追加ボーナスで取得した無償一次通貨の現在の数',
  `current_reward_amount` bigint NOT NULL COMMENT '広告視聴等の報酬で取得した無償一次通貨の現在の数',
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '無償一次通貨の変動契機',
  `trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_logging_no_index` (`created_at`,`logging_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='無償一次通貨ログ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_currency_paids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_currency_paids` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seq_no` bigint unsigned NOT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `currency_paid_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動した通貨テーブルのレコードID',
  `receipt_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'このレコードを生成した購入レシートID（購入の場合）',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `query` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'どういう変化が起きたか',
  `purchase_price` decimal(20,6) NOT NULL COMMENT '購入時の価格',
  `purchase_amount` bigint NOT NULL COMMENT '購入時に付与された個数',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `currency_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `before_amount` bigint NOT NULL COMMENT '変更前の有償一次通貨の数',
  `change_amount` bigint NOT NULL COMMENT '取得した有償一次通貨の数\n消費の場合は負',
  `current_amount` bigint NOT NULL COMMENT '変動後現在でユーザーがプラットフォームに所持している有償一次通貨の数\n単価関係ない総数（summaryに入れる数）',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '有償一次通貨の変動契機',
  `trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `currency_paid_id_index` (`currency_paid_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `receipt_unique_id_index` (`receipt_unique_id`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='有償一次通貨ログ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_currency_revert_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_currency_revert_histories` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'コメント',
  `log_trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガータイプ',
  `log_trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガーID',
  `log_trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのトリガー名',
  `log_request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '対象ログのリクエストID',
  `log_created_at` timestamp NOT NULL COMMENT '対象ログの作成日時',
  `log_change_paid_amount` bigint NOT NULL COMMENT '変更対象の有償通貨',
  `log_change_free_amount` bigint NOT NULL COMMENT '変更対象の無償通貨',
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガータイプ',
  `trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガーID',
  `trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガー名',
  `trigger_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'トリガー詳細',
  `request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_currency_revert_history_free_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_currency_revert_history_free_logs` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `log_currency_revert_history_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_revert_historiessのID',
  `log_currency_free_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実行した際のlog_currency_freesのID',
  `revert_log_currency_free_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_freesの返却対象としたログのID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログと無償一次通貨ログの紐付け';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_currency_revert_history_paid_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_currency_revert_history_paid_logs` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ユーザーID',
  `log_currency_revert_history_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_revert_historiesのID',
  `log_currency_paid_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実行した際のlog_currency_paidsのID',
  `revert_log_currency_paid_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'log_currency_paidsの返却対象としたログのID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='一次通貨返却ログと有償一次通貨ログの紐付け';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_stores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `log_stores` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `seq_no` bigint unsigned DEFAULT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `platform_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム側で定義しているproduct_id',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターテーブルのプロダクトID',
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `product_sub_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '実際の販売商品名',
  `raw_receipt` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '復号済み生レシートデータ',
  `raw_price_string` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントから送られてきた単価付き購入価格',
  `currency_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `receipt_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ユニークなID',
  `receipt_bundle_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `age` int NOT NULL COMMENT '年齢',
  `paid_amount` bigint NOT NULL COMMENT '有償一次通貨の付与量',
  `free_amount` bigint NOT NULL COMMENT '無償一次通貨の付与量',
  `purchase_price` decimal(20,6) NOT NULL COMMENT 'ストアから送られてきた実際の購入価格',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ショップ購入契機',
  `trigger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機に対応するID',
  `trigger_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '変動契機の日本語名',
  `trigger_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'そのほかの付与情報',
  `request_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'リクエスト識別ID',
  `nginx_request_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'nginxのリクエスト識別ID',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `opr_product_id_index` (`product_sub_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `trigger_id_index` (`trigger_id`),
  KEY `trigger_name_index` (`trigger_name`),
  KEY `user_id_currency_code_purchase_price_index` (`is_sandbox`,`usr_user_id`,`currency_code`,`purchase_price`),
  KEY `created_at_id_index` (`created_at`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ショップ購入ログ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_artwork_fragments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_artwork_fragments` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
  `mst_artwork_fragment_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artwork_fragments.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_artwork_fragment_id` (`usr_user_id`,`mst_artwork_fragment_id`),
  KEY `usr_artwork_fragments_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持している原画のかけら';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_artworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_artworks` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_artwork_id` (`usr_user_id`,`mst_artwork_id`),
  KEY `usr_artworks_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持している原画';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_avatar_frames`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_avatar_frames` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_avatar_frame_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usr_avatar_frames_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_avatars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_avatars` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_avatar_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usr_avatars_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_condition_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_condition_packs` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_pack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_packs.id',
  `start_date` timestamp NOT NULL COMMENT '購入可能期間開始日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_condition_packs_usr_user_id_mst_pack_id_unique` (`usr_user_id`,`mst_pack_id`),
  KEY `usr_condition_packs_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_currency_frees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_currency_frees` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ingame_amount` bigint NOT NULL DEFAULT '0' COMMENT 'ゲーム内・配布などで取得した無償一次通貨の所持数',
  `bonus_amount` bigint NOT NULL DEFAULT '0' COMMENT 'ショップ販売の追加付与で取得した無償一次通貨の所持数\n変動単価性の場合は発生しない',
  `reward_amount` bigint NOT NULL DEFAULT '0' COMMENT '広告視聴などで発生する報酬から取得した無償一次通貨の所持数',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_unique` (`usr_user_id`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する無償一次通貨の詳細';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_currency_paids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_currency_paids` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `seq_no` bigint unsigned NOT NULL COMMENT '登録した連番',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `left_amount` bigint NOT NULL COMMENT '同一単価・通貨での残所持数',
  `purchase_price` decimal(20,6) NOT NULL COMMENT '購入時のストアから送られてくる購入価格',
  `purchase_amount` bigint NOT NULL COMMENT '購入時に取得した有償一次通貨数',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `currency_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO 4217の通貨コード',
  `receipt_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_seq_no_unique` (`usr_user_id`,`seq_no`),
  UNIQUE KEY `platform_receipt_unique_id_unique` (`billing_platform`,`receipt_unique_id`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する有償一次通貨の詳細';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_currency_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_currency_summaries` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_amount_apple` bigint NOT NULL DEFAULT '0' COMMENT 'AppStoreで購入した有償一次通貨の所持数',
  `paid_amount_google` bigint NOT NULL DEFAULT '0' COMMENT 'GooglePlayで購入した有償一次通貨の所持数',
  `free_amount` bigint NOT NULL DEFAULT '0' COMMENT '無償一次通貨の所持数',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_unique` (`usr_user_id`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーの所持する通貨の集約データ';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_device_link_passwords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_device_link_passwords` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_id` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_password` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_id` (`auth_id`),
  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_device_link_socials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_device_link_socials` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `auth_type` tinyint NOT NULL,
  `auth_token` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_type_auth_token` (`auth_type`,`auth_token`),
  UNIQUE KEY `uk_usr_user_id_auth_type` (`usr_user_id`,`auth_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_devices` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_emblems`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_emblems` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usr_user_id_mst_emblem_id_index` (`usr_user_id`,`mst_emblem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_gacha_normals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_gacha_normals` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `opr_gacha_normal_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `diamond_draw_count` int NOT NULL DEFAULT '0',
  `ticket_draw_count` int NOT NULL DEFAULT '0',
  `ad_draw_count` int NOT NULL DEFAULT '0',
  `ad_draw_reset_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_gacha_normals_usr_user_id_opr_gacha_normal_id_unique` (`usr_user_id`,`opr_gacha_normal_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_gacha_supers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_gacha_supers` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `opr_gacha_super_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `draw_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_gacha_supers_usr_user_id_opr_gacha_super_id_unique` (`usr_user_id`,`opr_gacha_super_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_idle_incentives`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_idle_incentives` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `diamond_quick_receive_count` int unsigned NOT NULL DEFAULT '0',
  `ad_quick_receive_count` int unsigned NOT NULL DEFAULT '0' COMMENT '広告でのクイック獲得回数',
  `idle_started_at` timestamp NOT NULL COMMENT '放置開始時間',
  `diamond_quick_receive_at` timestamp NOT NULL DEFAULT '2000-01-01 00:00:00',
  `ad_quick_receive_at` timestamp NOT NULL COMMENT '広告でクイック獲得を実行した時刻',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_idle_incentives_usr_user_id_unique` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_items` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `amount` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_mst_item_id` (`usr_user_id`,`mst_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_achievement_progresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_achievement_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー「criterion_type:criterion_value」',
  `progress` bigint unsigned NOT NULL COMMENT '生涯累積進捗値',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_criterion_key` (`usr_user_id`,`criterion_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_achievements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_achievements` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_achievement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
  `status` tinyint unsigned NOT NULL COMMENT '0:未クリア, 1:クリア, 2:報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_mission_achievement_id` (`usr_user_id`,`mst_mission_achievement_id`),
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_beginner_progresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_beginner_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
  `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '初心者ミッション累積進捗値',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_criterion_key` (`usr_user_id`,`criterion_key`),
  KEY `usr_mission_beginner_progresses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_beginners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_beginners` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_beginner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_beginners.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_mission_beginner_id` (`usr_user_id`,`mst_mission_beginner_id`),
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_beginners_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_dailies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_dailies` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_daily_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_dailies.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_mission_daily_id` (`usr_user_id`,`mst_mission_daily_id`),
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_dailies_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_daily_bonuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_daily_bonuses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_daily_bonus_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_daily_bonuses.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_mission_daily_bonus_id` (`usr_user_id`,`mst_mission_daily_bonus_id`),
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_daily_bonuses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_daily_progresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_daily_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
  `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'デイリー累積進捗値',
  `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_criterion_key` (`usr_user_id`,`criterion_key`),
  KEY `usr_mission_daily_progresses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_recent_additions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_recent_additions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mission_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションタイプ',
  `latest_release_key` int NOT NULL COMMENT '判定済みの中で最新のリリースキー',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mission_type` (`usr_user_id`,`mission_type`),
  KEY `usr_mission_recent_additions_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_statuses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `beginner_mission_status` smallint unsigned NOT NULL DEFAULT '0' COMMENT '初心者ミッション未クリア: 0 初心者ミッションクリア: 1',
  `mission_unlocked_at` timestamp NULL DEFAULT NULL COMMENT 'ミッション解放日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_weeklies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_weeklies` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_mission_weekly_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_weeklies.id',
  `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
  `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
  `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
  `latest_update_at` timestamp NOT NULL COMMENT '週跨ぎリセット判定用。ステータス変更をした最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_mission_weekly_id` (`usr_user_id`,`mst_mission_weekly_id`),
  KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
  KEY `usr_mission_weeklies_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_mission_weekly_progresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_mission_weekly_progresses` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
  `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ウィークリー累積進捗値',
  `latest_update_at` timestamp NOT NULL COMMENT '週跨ぎリセット判定用。ステータス変更をした最終更新日時',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_criterion_key` (`usr_user_id`,`criterion_key`),
  KEY `usr_mission_weekly_progresses_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_outpost_enhancements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_outpost_enhancements` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_outpost_enhancement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_outpost_enhancements_unique` (`usr_user_id`,`mst_outpost_id`,`mst_outpost_enhancement_id`),
  KEY `usr_outpost_enhancements_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_outposts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_outposts` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_outpost_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_artwork_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_artworks.id',
  `is_used` tinyint NOT NULL DEFAULT '0' COMMENT '使用中かどうか',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_outposts_unique` (`usr_user_id`,`mst_outpost_id`),
  KEY `usr_outposts_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_parties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_parties` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `party_no` int unsigned NOT NULL COMMENT 'パーティ番号',
  `party_name` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'パーティ名',
  `usr_unit_id_1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '1スロット目のユーザーユニットID',
  `usr_unit_id_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '2スロット目のユーザーユニットID',
  `usr_unit_id_3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '3スロット目のユーザーユニットID',
  `usr_unit_id_4` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '4スロット目のユーザーユニットID',
  `usr_unit_id_5` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '5スロット目のユーザーユニットID',
  `usr_unit_id_6` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '6スロット目のユーザーユニットID',
  `usr_unit_id_7` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '7スロット目のユーザーユニットID',
  `usr_unit_id_8` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '8スロット目のユーザーユニットID',
  `usr_unit_id_9` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '9スロット目のユーザーユニットID',
  `usr_unit_id_10` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '10スロット目のユーザーユニットID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_received_unit_encyclopedia_rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_received_unit_encyclopedia_rewards` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `mst_unit_encyclopedia_reward_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_unit_encyclopedia_rewards.id',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id_mst_unit_encyclopedia_reward_id` (`usr_user_id`,`mst_unit_encyclopedia_reward_id`),
  KEY `usr_received_unit_encyclopedia_rewards_usr_user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーが受け取った図鑑報酬のID';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_shop_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_shop_items` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_usersのid',
  `mst_shop_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_shop_itemsのid',
  `trade_count` int unsigned NOT NULL COMMENT '交換回数',
  `trade_total_count` int unsigned NOT NULL DEFAULT '0' COMMENT '累計交換回数',
  `last_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_shop_items_usr_user_id_mst_shop_item_id_unique` (`usr_user_id`,`mst_shop_item_id`),
  KEY `user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_stage_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_stage_sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_valid` tinyint unsigned NOT NULL DEFAULT '0',
  `party_no` int unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
  `continue_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'コンティニュー回数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_stage_sessions_usr_user_id_unique` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_stages` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clear_status` tinyint NOT NULL,
  `clear_count` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_stages_usr_user_id_mst_stage_id_unique` (`usr_user_id`,`mst_stage_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_store_allowances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_store_allowances` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ストアのプロダクトID',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_store_product_id',
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay のどちらで購入フローをしようとしているか',
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_platform_product_id_unique` (`usr_user_id`,`billing_platform`,`product_id`),
  KEY `user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ購入の許可確認状態';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_store_infos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_store_infos` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL COMMENT '年齢',
  `paid_price` bigint NOT NULL DEFAULT '0' COMMENT '支払ったJPYの金額',
  `renotify_at` timestamp NULL DEFAULT NULL COMMENT '次回年齢再確認する日時\nNULLなら再確認しなくて良い（20歳以上）',
  `total_vip_point` bigint NOT NULL DEFAULT '0' COMMENT '商品購入時に獲得したVIPポイントの合計',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_unique` (`usr_user_id`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ登録情報';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_store_product_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_store_product_histories` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
  `receipt_unique_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォームごとの購入毎ユニークなIDを入れる',
  `os_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'OSプラットフォーム',
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ユーザーの使用しているデバイス識別子',
  `age` int NOT NULL COMMENT '年齢',
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '購入対象のproduct_sub_id',
  `platform_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'プラットフォーム側で定義しているproduct_id',
  `mst_store_product_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'マスターテーブルのプロダクトID',
  `currency_code` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'クライアントから送られてきた通貨コード',
  `receipt_bundle_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'レシート記載、ストアから送られてきた商品のバンドルID',
  `paid_amount` bigint NOT NULL COMMENT '有償一次通貨の付与量',
  `free_amount` bigint NOT NULL COMMENT '無償一次通貨の付与量',
  `purchase_price` decimal(20,6) NOT NULL COMMENT 'ストアから送られてきた実際の購入価格',
  `price_per_amount` decimal(20,8) NOT NULL COMMENT '単価',
  `vip_point` bigint NOT NULL COMMENT '商品購入時に獲得したVIPポイント',
  `is_sandbox` tinyint NOT NULL COMMENT 'サンドボックス・テスト課金から購入したら1, 本番購入なら0',
  `billing_platform` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'AppStore / GooglePlay',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `receipt_unique_id_billing_platform_unique` (`receipt_unique_id`,`billing_platform`),
  KEY `user_id_index` (`usr_user_id`),
  KEY `user_id_sandbox_index` (`usr_user_id`,`is_sandbox`),
  KEY `user_id_deleted_at_index` (`usr_user_id`,`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ユーザーのショップ購入履歴';
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_store_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_store_products` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_usersのid',
  `product_sub_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_productsのid',
  `purchase_count` int unsigned NOT NULL COMMENT '購入回数',
  `purchase_total_count` int unsigned NOT NULL DEFAULT '0' COMMENT '累計購入回数',
  `last_reset_at` timestamp NOT NULL COMMENT '最終リセット日時',
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id_index` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_units` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mst_unit_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `level` int unsigned NOT NULL DEFAULT '1',
  `rank` int unsigned NOT NULL DEFAULT '1',
  `grade_level` int unsigned NOT NULL DEFAULT '0',
  `atk` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mst_unit_and_usr_id_index` (`usr_user_id`,`mst_unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_user_buy_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_user_buy_counts` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `daily_buy_stamina_ad_count` int NOT NULL DEFAULT '0',
  `daily_buy_stamina_ad_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usr_user_buy_counts_usr_user_id_unique` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_user_logins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_user_logins` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
  `first_login_at` timestamp NOT NULL COMMENT '初回ログイン日時',
  `last_login_at` timestamp NOT NULL COMMENT '最終ログイン日時',
  `login_count` int unsigned NOT NULL DEFAULT '0' COMMENT 'ログイン回数',
  `login_day_count` int NOT NULL DEFAULT '0' COMMENT '生涯累計のログイン日数',
  `login_continue_day_count` int NOT NULL DEFAULT '0' COMMENT '連続ログイン日数。連続ログインが途切れたら0にリセットする。',
  `comeback_day_count` int NOT NULL DEFAULT '0' COMMENT '最終ログインからの復帰にかかった日数',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_user_parameters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_user_parameters` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int unsigned NOT NULL DEFAULT '1',
  `exp` bigint NOT NULL DEFAULT '0',
  `coin` bigint unsigned NOT NULL DEFAULT '0',
  `stamina` int unsigned NOT NULL DEFAULT '0',
  `stamina_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_user_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_user_profiles` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `usr_user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `my_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_change_name` tinyint unsigned NOT NULL DEFAULT '0',
  `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アバターとして設定したユニットのID',
  `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'エンブレムID',
  `mst_avatar_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `mst_avatar_frame_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `name_update_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`),
  UNIQUE KEY `usr_user_profiles_my_id_unique` (`my_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usr_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usr_users` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` smallint unsigned NOT NULL DEFAULT '1',
  `tutorial_status` smallint unsigned NOT NULL DEFAULT '0',
  `tos_version` smallint unsigned NOT NULL DEFAULT '0',
  `privacy_policy_version` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

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
