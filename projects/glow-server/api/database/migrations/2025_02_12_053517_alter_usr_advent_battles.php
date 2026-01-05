<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    //CREATE TABLE `usr_advent_battles` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_advent_battles.id',
    //  `max_score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '最高スコア',
    //  `total_score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '累計スコア',
    //  `challenge_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(リセットなし)',
    //  `reset_challenge_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(デイリーリセット対象)',
    //  `reset_ad_challenge_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '広告視聴での挑戦回数(デイリーリセット対象)',
    //  `max_received_max_score_reward` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '受取済みの最高スコア報酬の最高スコア数値',
    //  `received_rank_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_advent_battle_reward_groups.id',
    //  `received_raid_reward_group_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mst_advent_battle_reward_groups.id',
    //  `is_ranking_reward_received` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '順位報酬、またはランク報酬受け取り済みか',
    //  `is_excluded_ranking` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ランキングから除外されているか',
    //  `latest_reset_at` timestamp NULL DEFAULT NULL COMMENT 'リセット日時',
    //  `created_at` timestamp NULL DEFAULT NULL,
    //  `updated_at` timestamp NULL DEFAULT NULL,
    //  PRIMARY KEY (`usr_user_id`,`mst_advent_battle_id`) /*T![clustered_index] CLUSTERED */,
    //  UNIQUE KEY `usr_advent_battles_id_unique` (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->unsignedInteger('clear_count')->default(0)->comment('クリア回数')->after('reset_ad_challenge_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->dropColumn('clear_count');
        });
    }
};
