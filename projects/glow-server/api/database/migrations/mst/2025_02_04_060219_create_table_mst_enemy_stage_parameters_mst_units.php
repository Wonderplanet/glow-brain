<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
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
    //     `release_key` int NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    //   CREATE TABLE `mst_enemy_stage_parameters` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `mst_enemy_character_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `character_unit_kind` enum('Normal','Formidable','Boss','AdventBattleBoss') COLLATE utf8mb4_bin NOT NULL,
    //     `role_type` enum('None','Attack','Balance','Defense','Support','Unique','Technical','Special') COLLATE utf8mb4_bin NOT NULL,
    //     `color` enum('None','Colorless','Red','Blue','Yellow','Green') COLLATE utf8mb4_bin NOT NULL,
    //     `sort_order` int NOT NULL,
    //     `hp` int NOT NULL,
    //     `damage_knock_back_count` int NOT NULL,
    //     `move_speed` int NOT NULL,
    //     `well_distance` double NOT NULL,
    //     `attack_power` int NOT NULL,
    //     `attack_combo_cycle` int NOT NULL,
    //     `mst_unit_ability_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT '',
    //     `bounding_range_front` double NOT NULL,
    //     `bounding_range_back` double NOT NULL,
    //     `drop_battle_point` int NOT NULL,
    //     `mst_transformation_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `transformation_condition_type` enum('None','HpPercentage','StageTime') COLLATE utf8mb4_bin NOT NULL,
    //     `transformation_condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `death_effect_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MstEnemyStageParameterからboundingRangeFront, boundingRangeBack列を削除
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
        });

        // MstUnitからboundingRangeFront,boundingRangeBack列を削除
        Schema::table('mst_units', function (Blueprint $table) {
            $table->dropColumn('bounding_range_front');
            $table->dropColumn('bounding_range_back');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // MstEnemyStageParameterにboundingRangeFront, boundingRangeBack列を追加
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->double('bounding_range_back', 8, 2)->after('mst_unit_ability_id1');
            $table->double('bounding_range_front', 8, 2)->after('mst_unit_ability_id1');
        });

        // MstUnitにboundingRangeFront,boundingRangeBack列を追加
        Schema::table('mst_units', function (Blueprint $table) {
            $table->double('bounding_range_back', 8, 2)->after('mst_unit_ability_id1');
            $table->double('bounding_range_front', 8, 2)->after('mst_unit_ability_id1');
        });
    }
};
