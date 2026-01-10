<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_enemy_characters` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_attack_hit_onomatopeia_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `is_displayed_encyclopedia` tinyint NOT NULL DEFAULT '0'
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // CREATE TABLE `mst_attack_elements` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_attack_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int NOT NULL,
    //     `attack_delay` int NOT NULL,
    //     `attack_type` enum('None','Direct','Deck') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_start_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_start_parameter` double(8,2) NOT NULL,
    //     `range_end_type` enum('Distance','Koma','KomaLine','Page') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `range_end_parameter` double(8,2) NOT NULL,
    //     `max_target_count` int NOT NULL,
    //     `target` enum('Friend','Foe','Self') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_type` enum('All','Character','Outpost') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_colors` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_roles` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `damage_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //     `hit_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Normal',
    //     `hit_parameter1` int unsigned NOT NULL DEFAULT '0',
    //     `hit_parameter2` int unsigned NOT NULL DEFAULT '0',
    //     `is_hit_stop` tinyint NOT NULL DEFAULT '0',
    //     `probability` int NOT NULL,
    //     `power_parameter_type` enum('Percentage','Fixed','MaxHpPercentage') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `power_parameter` int NOT NULL,
    //     `effect_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None',
    //     `effective_count` int NOT NULL,
    //     `effective_duration` int NOT NULL,
    //     `effect_parameter` int NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // CREATE TABLE `mst_units` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `fragment_mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `color` enum('Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Colorless' COMMENT '属性',
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `attack_range_type` enum('Short','Middle','Long') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `unit_label` enum('DropR','DropSR','DropSSR','DropUR','PremiumR','PremiumSR','PremiumSSR','PremiumUR','FestivalUR') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `has_specific_rank_up` tinyint NOT NULL DEFAULT '0' COMMENT 'キャラ個別のランクアップ設定を使うかどうか',
    //     `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `rarity` enum('N','R','SR','SSR','UR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `sort_order` int unsigned NOT NULL,
    //     `summon_cost` int unsigned NOT NULL,
    //     `summon_cool_time` int unsigned NOT NULL,
    //     `special_attack_initial_cool_time` int unsigned NOT NULL,
    //     `special_attack_cool_time` int unsigned NOT NULL,
    //     `min_hp` int unsigned NOT NULL,
    //     `max_hp` int unsigned NOT NULL,
    //     `damage_knock_back_count` int unsigned NOT NULL,
    //     `move_speed` int unsigned NOT NULL,
    //     `well_distance` double(8,2) NOT NULL,
    //     `min_attack_power` int unsigned NOT NULL,
    //     `max_attack_power` int unsigned NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `bounding_range_front` double(8,2) NOT NULL,
    //     `bounding_range_back` double(8,2) NOT NULL,
    //     `is_encyclopedia_special_attack_position_right` tinyint unsigned NOT NULL DEFAULT '0',
    //     `mst_attack_hit_onomatopeia_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    // CREATE TABLE `mst_attack_hit_onomatopeia_groups` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `asset_key1` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `asset_key2` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `asset_key3` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // mst_enemy_charactersからmstAttackHitOnomatopeiaGroupId列削除
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('mst_attack_hit_onomatopeia_group_id');
        });

        //mst_attack_elementsのhitParameter2の後にhitOnomatopoeiaGroupId列(varchar255)追加
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->string('hit_onomatopoeia_group_id')->after('hit_parameter2');
        });

        // mst_unitsからmstAttackHitOnomatopeiaGroupId列削除
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('mst_attack_hit_onomatopeia_group_id');
        });

        // // mst_attack_hit_onomatopeia_groupsテーブル名をmst_attack_hit_onomatopoeia_groupsに変更
        // Schema::rename('mst_attack_hit_onomatopeia_groups', 'mst_attack_hit_onomatopoeia_groups');

        // mst_attack_hit_onomatopeia_groupsではなくmst_attack_hit_onomatopoeia_groupsで同じスキーマで新規テーブル作成
        Schema::create('mst_attack_hit_onomatopoeia_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('asset_key1')->default('');
            $table->string('asset_key2')->default('');
            $table->string('asset_key3')->default('');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_enemy_charactersにmstAttackHitOnomatopeiaGroupId列追加
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->string('mst_attack_hit_onomatopeia_group_id')->after('asset_key');
        });

        //mst_attack_elementsのhitOnomatopoeiaGroupId列削除
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->dropColumn('hit_onomatopoeia_group_id');
        });

        // mst_unitsにmstAttackHitOnomatopeiaGroupId列追加
        Schema::table('mst_units', function (Blueprint $table) {
            $table->string('mst_attack_hit_onomatopeia_group_id')->after('is_encyclopedia_special_attack_position_right');
        });

        // // mst_attack_hit_onomatopoeia_groupsテーブル名をmst_attack_hit_onomatopeia_groupsに変更
        // Schema::rename('mst_attack_hit_onomatopoeia_groups', 'mst_attack_hit_onomatopeia_groups');

        // mst_attack_hit_onomatopoeia_groupsテーブル削除
        Schema::dropIfExists('mst_attack_hit_onomatopoeia_groups');
    }
};
