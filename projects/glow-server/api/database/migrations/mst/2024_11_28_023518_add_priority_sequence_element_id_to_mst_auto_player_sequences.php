<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_auto_player_sequences` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `sequence_element_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `sequence_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `action_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `action_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `summon_animation_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `summon_count` int NOT NULL DEFAULT '0',
    //  `summon_interval` int NOT NULL DEFAULT '0',
    //  `action_delay` int NOT NULL DEFAULT '0',
    //  `summon_position` double NOT NULL DEFAULT '0',
    //  `move_start_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `move_start_condition_value` bigint NOT NULL DEFAULT '0',
    //  `move_stop_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `move_stop_condition_value` bigint NOT NULL DEFAULT '0',
    //  `move_restart_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `move_restart_condition_value` bigint NOT NULL DEFAULT '0',
    //  `move_loop_count` int NOT NULL DEFAULT '0',
    //  `last_boss_trigger` tinyint NOT NULL DEFAULT '0',
    //  `override_drop_battle_point` int DEFAULT NULL,
    //  `enemy_hp_coef` double NOT NULL DEFAULT '0',
    //  `enemy_attack_coef` double NOT NULL DEFAULT '0',
    //  `enemy_speed_coef` double NOT NULL DEFAULT '0',
    //  `deactivation_condition_type` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //  `deactivation_condition_value` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `is_summon_unit_outpost_damage_invalidation` tinyint unsigned NOT NULL DEFAULT '0',
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    // 変更内容
    // priority_sequence_element_id カラムを追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->string('priority_sequence_element_id', 255)->nullable()->default(null)->after('sequence_group_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->dropColumn('priority_sequence_element_id');
        });
    }
};
