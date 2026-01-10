<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_items` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `rarity` enum('N','R','SR','SSR','UR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'mst_series.id',
    //     `sort_order` int NOT NULL DEFAULT '0',
    //     `start_date` timestamp NOT NULL,
    //     `end_date` timestamp NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `destination_opr_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     PRIMARY KEY (`id`),
    //     KEY `mst_items_item_type_index` (`type`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `opr_gachas` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `gacha_type` enum('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal' COMMENT 'ガシャタイプ',
    //     `upper_group` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None' COMMENT '天井設定区分',
    //     `enable_ad_play` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で回せるか',
    //     `enable_add_ad_play_upper` tinyint(1) NOT NULL DEFAULT '0' COMMENT '広告で天井を動かすか',
    //     `ad_play_interval_time` int unsigned DEFAULT NULL COMMENT '広告で回すことができるインターバル時間(設定単位は分)',
    //     `multi_draw_count` int unsigned NOT NULL DEFAULT '1' COMMENT 'N連の指定',
    //     `multi_fixed_prize_count` smallint unsigned DEFAULT '0' COMMENT 'N連の確定枠数',
    //     `daily_play_limit_count` int unsigned DEFAULT NULL COMMENT '１日に回すことができる上限数',
    //     `total_play_limit_count` int unsigned DEFAULT NULL COMMENT '回すことができる上限数',
    //     `daily_ad_limit_count` int unsigned DEFAULT NULL COMMENT '1日に広告で回すことができる上限数',
    //     `total_ad_limit_count` int unsigned DEFAULT NULL COMMENT '広告で回すことができる上限数',
    //     `prize_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gacha_prizes.group_id',
    //     `fixed_prize_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '確定枠(opr_gacha_prizes.group_id)',
    //     `appearance_condition` enum('Always','HasTicket') COLLATE utf8mb4_bin NOT NULL DEFAULT 'Always' COMMENT '登場条件',
    //     `start_at` timestamp NOT NULL COMMENT '開始日時',
    //     `end_at` timestamp NOT NULL COMMENT '終了日時',
    //     `display_mst_unit_id` text COLLATE utf8mb4_bin COMMENT '表示に使用するピックアップユニットIDを指定する',
    //     `display_information_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'ガチャ詳細用お知らせID',
    //     `gacha_priority` int NOT NULL DEFAULT '1' COMMENT 'バナー表示順',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // mst_items.item_typeにGachaMedalを追加
    // opr_gachas.gacha_typeにMedalを追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_items', function (Blueprint $table) {
            DB::statement("ALTER TABLE mst_items MODIFY type ENUM('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment','GachaMedal');");
        });

        Schema::table('opr_gachas', function (Blueprint $table) {
            DB::statement("ALTER TABLE opr_gachas MODIFY gacha_type ENUM('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly','Medal');");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_items', function (Blueprint $table) {
            DB::statement("ALTER TABLE mst_items MODIFY type ENUM('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc','RankUpMemoryFragment');");
        });

        Schema::table('opr_gachas', function (Blueprint $table) {
            DB::statement("ALTER TABLE opr_gachas MODIFY gacha_type ENUM('Normal','Premium','Pickup','Free','Ticket','Festival','PaidOnly');");
        });
    }
};
