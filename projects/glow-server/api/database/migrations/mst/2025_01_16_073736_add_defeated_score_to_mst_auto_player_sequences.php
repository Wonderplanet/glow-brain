<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_auto_player_sequences` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `sequence_element_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `sequence_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `priority_sequence_element_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
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

    // CREATE TABLE `mst_advent_battles` (
    //  `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //  `mst_in_game_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //  `advent_battle_type` enum('ScoreChallenge','Raid') COLLATE utf8mb4_bin NOT NULL COMMENT '降臨バトルタイプ',
    //  `initial_battle_point` int NOT NULL DEFAULT '0',
    //  `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
    //  `event_bonus_group_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'mst_event_bonus_units.event_bonus_group_id',
    //  `challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の挑戦可能回数',
    //  `ad_challengeable_count` smallint unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告視聴での挑戦可能回数',
    //  `display_mst_unit_id1` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所1に表示するキャラ',
    //  `display_mst_unit_id2` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所2に表示するキャラ',
    //  `display_mst_unit_id3` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '降臨バトルトップ場所3に表示するキャラ',
    //  `exp` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得リーダーEXP',
    //  `coin` int unsigned NOT NULL DEFAULT '0' COMMENT '獲得コイン',
    //  `start_at` timestamp NOT NULL COMMENT '降臨バトル開始日',
    //  `end_at` timestamp NOT NULL COMMENT '降臨バトル終了日',
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `score_addition_type` enum('AllEnemiesAndOutPost','AllEnemies','TargetEnemy') COLLATE utf8mb4_bin NOT NULL DEFAULT 'AllEnemiesAndOutPost' COMMENT 'スコア加算タイプ',
    //  `score_addition_target_mst_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'TargetEnemy時の対象MstId',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->integer('defeated_score')->default(0)->comment('撃破スコア')->after('override_drop_battle_point');
        });

        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->string('score_addition_type')->default('AllEnemiesAndOutPost')->change();
            $table->dropColumn('score_addition_target_mst_enemy_stage_parameter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_auto_player_sequences', function (Blueprint $table) {
            $table->dropColumn('defeated_score');
        });

        DB::statement("ALTER TABLE `mst_advent_battles` MODIFY COLUMN `score_addition_type` ENUM('AllEnemiesAndOutPost','AllEnemies','TargetEnemy') NOT NULL DEFAULT 'AllEnemiesAndOutPost';");
        Schema::table('mst_advent_battles', function (Blueprint $table) {
            $table->string('score_addition_target_mst_enemy_stage_parameter_id')->default('')->after('score_addition_type');
        });
    }
};
