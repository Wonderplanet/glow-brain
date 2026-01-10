<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_advent_battles_i18n` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_advent_battles.id',
    //     `language` enum('ja') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
    //     `name` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '名前',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // CREATE TABLE `mst_enemy_stage_parameters` (
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
    //     `drop_battle_point` int NOT NULL,
    //     `mst_transformation_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `transformation_condition_type` enum('None','HpPercentage','StageTime') COLLATE utf8mb4_bin NOT NULL,
    //     `transformation_condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `death_effect_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'Normal',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

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
    //     `hit_onomatopoeia_group_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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

    // CREATE TABLE `mst_shop_passes` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `opr_product_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `is_display_expiration` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '販売の有効期限を表示するかどうか 0:表示しない 1:表示する',
    //     `pass_duration_days` int unsigned NOT NULL COMMENT 'パスの有効日数',
    //     `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `uk_opr_product_id` (`opr_product_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // CREATE TABLE `mst_auto_player_sequences` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `sequence_element_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `sequence_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `priority_sequence_element_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    //     `condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `action_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `action_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `action_value2` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `summon_animation_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `summon_count` int NOT NULL DEFAULT '0',
    //     `summon_interval` int NOT NULL DEFAULT '0',
    //     `action_delay` int NOT NULL DEFAULT '0',
    //     `summon_position` double NOT NULL DEFAULT '0',
    //     `move_start_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `move_start_condition_value` bigint NOT NULL DEFAULT '0',
    //     `move_stop_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `move_stop_condition_value` bigint NOT NULL DEFAULT '0',
    //     `move_restart_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `move_restart_condition_value` bigint NOT NULL DEFAULT '0',
    //     `move_loop_count` int NOT NULL DEFAULT '0',
    //     `last_boss_trigger` tinyint NOT NULL DEFAULT '0',
    //     `aura_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'Default',
    //     `override_drop_battle_point` int DEFAULT NULL,
    //     `defeated_score` int NOT NULL DEFAULT '0' COMMENT '撃破スコア',
    //     `enemy_hp_coef` double NOT NULL DEFAULT '0',
    //     `enemy_attack_coef` double NOT NULL DEFAULT '0',
    //     `enemy_speed_coef` double NOT NULL DEFAULT '0',
    //     `deactivation_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `deactivation_condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `is_summon_unit_outpost_damage_invalidation` tinyint unsigned NOT NULL DEFAULT '0',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;



    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_advent_battles_i18n', function (Blueprint $table) {
            $table->string('boss_description')->default('')->after('name');
        });

        // mst_enemy_stage_parameters
        // death_effect_type列削除,transformation_condition_typeをvarchar(255)に変更。defaultはNone
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->dropColumn('death_effect_type');
            $table->string('transformation_condition_type', 255)->default('None')->change();
        });

        // mst_attack_elements
        // effect_parameterをintからfloat(double(8,2))に変更
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->double('effect_parameter', 8, 2)->default(0)->change();
        });

        Schema::create('mst_special_role_level_up_attack_elements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('mst_attack_element_id')->default('');
            $table->integer('min_effective_count');
            $table->integer('max_effective_count');
            $table->integer('min_effective_duration');
            $table->integer('max_effective_duration');
            $table->decimal('min_effect_parameter', 10, 2);
            $table->decimal('max_effect_parameter', 10, 2);
        });

        // mst_shop_passes
        // assetKeyの後に、shop_pass_cell_colorをvarchar(255)に追加
        Schema::table('mst_shop_passes', function (Blueprint $table) {
            $table->string('shop_pass_cell_color')->default('')->after('asset_key');
        });

        // mst_auto_player_sequences
        // aura_typeの後に、death_typeをvarchar(255)defaultNormalで
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->string('death_type', 255)->after('aura_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_advent_battles_i18n
        Schema::table('mst_advent_battles_i18n', function (Blueprint $table) {
            $table->dropColumn('boss_description');
        });

        // mst_enemy_stage_parameters
        Schema::table('mst_enemy_stage_parameters', function (Blueprint $table) {
            $table->string('death_effect_type', 255)->default('Normal');
        });
        DB::statement('ALTER TABLE mst_enemy_stage_parameters MODIFY transformation_condition_type enum("None","HpPercentage","StageTime") COLLATE utf8mb4_bin NOT NULL DEFAULT "None"');

        // mst_attack_elements
        Schema::table('mst_attack_elements', function (Blueprint $table) {
            $table->integer('effect_parameter')->default(0)->change();
        });

        // mst_special_role_level_up_attack_elements
        Schema::dropIfExists('mst_special_role_level_up_attack_elements');

        // mst_shop_passes
        Schema::table('mst_shop_passes', function (Blueprint $table) {
            $table->dropColumn('shop_pass_cell_color');
        });

        // mst_auto_player_sequences
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->dropColumn('death_type');
        });
    }
};
