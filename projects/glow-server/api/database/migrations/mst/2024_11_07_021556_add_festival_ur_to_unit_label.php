<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_units` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `color` enum('Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colorless' COMMENT '属性',
    //  `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `attack_range_type` enum('Short','Middle','Long') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //  `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `sort_order` int unsigned NOT NULL,
    //  `summon_cost` int unsigned NOT NULL,
    //  `summon_cool_time` int unsigned NOT NULL,
    //  `special_attack_initial_cool_time` int unsigned NOT NULL,
    //  `special_attack_cool_time` int unsigned NOT NULL,
    //  `min_hp` int unsigned NOT NULL,
    //  `max_hp` int unsigned NOT NULL,
    //  `damage_knock_back_count` int unsigned NOT NULL,
    //  `move_speed` int unsigned NOT NULL,
    //  `well_distance` double(8,2) NOT NULL,
    //  `min_attack_power` int unsigned NOT NULL,
    //  `max_attack_power` int unsigned NOT NULL,
    //  `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `bounding_range_front` double(8,2) NOT NULL,
    //  `bounding_range_back` double(8,2) NOT NULL,
    //  `is_encyclopedia_special_attack_position_right` tinyint unsigned NOT NULL DEFAULT '0',
    //  `release_key` int NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_grade_ups` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DropR',
    //  `grade_level` int unsigned NOT NULL,
    //  `require_amount` int unsigned NOT NULL COMMENT 'グレードアップに必要なかけら数',
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`),
    //  UNIQUE KEY `uk_unit_label_grade_level` (`unit_label`,`grade_level`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_grade_coefficients` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DropR' COMMENT 'ユニットラベル',
    //  `grade_level` int unsigned NOT NULL,
    //  `coefficient` int unsigned NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_level_ups` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DropR',
    //  `level` int NOT NULL,
    //  `required_coin` int NOT NULL,
    //  `release_key` int NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`),
    //  UNIQUE KEY `uk_unit_label_level` (`unit_label`,`level`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_rank_ups` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `rank` int NOT NULL,
    //  `amount` int NOT NULL,
    //  `require_level` int NOT NULL,
    //  `release_key` int NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`),
    //  UNIQUE KEY `uk_unit_label_rank` (`unit_label`,`rank`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_unit_fragment_converts` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `unit_label` enum('DropN','DropR','DropSR','DropSSR','DropUR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') COLLATE utf8mb4_bin NOT NULL,
    //  `convert_amount` int unsigned NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`),
    //  UNIQUE KEY `mst_unit_fragment_converts_rarity_unique` (`unit_label`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * 変更内容
     *
     * unit_labelにFestivalUR追加
     */

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $resourceTypes = [
            'DropN',
            'DropR',
            'DropSR',
            'DropSSR',
            'DropUR',
            'PremiumN',
            'PremiumR',
            'PremiumSR',
            'PremiumSSR',
            'PremiumUR',
            'FestivalUR',
        ];
        $unitLabelString = "'" . implode("', '", $resourceTypes) . "'";
        DB::statement("ALTER TABLE mst_units MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_coefficients MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_level_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_rank_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_fragment_converts MODIFY COLUMN unit_label ENUM($unitLabelString) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $resourceTypes = [
            'DropN',
            'DropR',
            'DropSR',
            'DropSSR',
            'DropUR',
            'PremiumN',
            'PremiumR',
            'PremiumSR',
            'PremiumSSR',
            'PremiumUR',
        ];
        $unitLabelString = "'" . implode("', '", $resourceTypes) . "'";
        DB::statement("ALTER TABLE mst_units MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_grade_coefficients MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_level_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_rank_ups MODIFY COLUMN unit_label ENUM ($unitLabelString) NOT NULL");
        DB::statement("ALTER TABLE mst_unit_fragment_converts MODIFY COLUMN unit_label ENUM($unitLabelString) NOT NULL");
    }
};
