<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_in_games` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `mst_auto_player_sequence_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `bgm_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `loop_background_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `player_outpost_asset_key` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `mst_page_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `mst_enemy_outpost_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `time_limit` int NOT NULL,
    //     `mst_defense_target_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
    //     `boss_mst_enemy_stage_parameter_id` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    //     `normal_enemy_hp_coef` decimal(10,2) NOT NULL,
    //     `normal_enemy_attack_coef` decimal(10,2) NOT NULL,
    //     `normal_enemy_speed_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_hp_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_attack_coef` decimal(10,2) NOT NULL,
    //     `boss_enemy_speed_coef` decimal(10,2) NOT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
    // CREATE TABLE `mst_stage_event_settings` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `mst_stage_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'mst_stages.id',
    //     `stage_event_type` enum('None','SpeedAttack') COLLATE utf8mb4_bin NOT NULL DEFAULT 'None',
    //     `reset_type` enum('Daily') COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'リセットタイプ',
    //     `clearable_count` int DEFAULT NULL COMMENT 'クリア可能回数',
    //     `ad_challenge_count` int NOT NULL DEFAULT '0' COMMENT '広告視聴で挑戦できる回数',
    //     `mst_stage_rule_group_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'mst_stage_event_rules.group_id',
    //     `start_at` timestamp NOT NULL COMMENT '開始日時',
    //     `end_at` timestamp NOT NULL COMMENT '終了日時',
    //     `release_key` bigint NOT NULL,
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `uk_mst_stage_id` (`mst_stage_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // mst_in_gamesのtime_limit列を削除
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->dropColumn('time_limit');
        });

        // mst_stage_event_settingsのstage_event_type列を削除
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->dropColumn('stage_event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_in_gamesのtime_limit列を追加
        Schema::table('mst_in_games', function (Blueprint $table) {
            $table->integer('time_limit')->default(0)->after('mst_enemy_outpost_id');
        });

        // mst_stage_event_settingsのstage_event_type列を追加
        Schema::table('mst_stage_event_settings', function (Blueprint $table) {
            $table->enum('stage_event_type', ['None', 'SpeedAttack'])->default('None')->after('mst_stage_id');
        });
    }
};
