<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    // 変更前

    // CREATE TABLE `mst_units` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `role_type` enum('Attack','Balance','Defense','Support','Unique') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `attack_range_type` enum('Short','Middle','Long') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_label` enum('DropN','DropR','DropSR','DropSSR','PremiumN','PremiumR','PremiumSR','PremiumSSR','PremiumUR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `image_id` bigint unsigned NOT NULL,
    //     `mst_series_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `summon_cost` int unsigned NOT NULL,
    //     `summon_cool_time` int unsigned NOT NULL,
    //     `min_hp` int unsigned NOT NULL,
    //     `max_hp` int unsigned NOT NULL,
    //     `damage_knock_back_count` int unsigned NOT NULL,
    //     `move_speed` int unsigned NOT NULL,
    //     `well_distance` double(8,2) NOT NULL,
    //     `min_attack_power` int unsigned NOT NULL,
    //     `max_attack_power` int unsigned NOT NULL,
    //     `attack_combo_cycle` int unsigned NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `bounding_range_front` double(8,2) NOT NULL,
    //     `bounding_range_back` double(8,2) NOT NULL,
    //     `series_asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `mst_enemy_characters_i18n` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_enemy_character_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // MstUnit
    // unit_labelの後にあるimageIdを削除
    // boundingRangeBackの後にisEncyclopediaSpecialAttackPositionRight(bool)を追加

    // MstEnemyCharacterI18n
    // nameの後にdescription(string)を追加


    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('image_id');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_encyclopedia_special_attack_position_right')->default(0)->after('bounding_range_back');
        });

        Schema::table('mst_enemy_characters_i18n', function (Blueprint $table) {
            $table->string('description')->nullable(false)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_units', function (Blueprint $table) {
            $table->unsignedBigInteger('image_id')->after('unit_label');
        });

        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('is_encyclopedia_special_attack_position_right');
        });

        Schema::table('mst_enemy_characters_i18n', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
