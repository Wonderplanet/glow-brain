<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_advent_battles` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //  `mst_advent_battle_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_advent_battles.id',
    //  `max_score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '最高スコア',
    //  `total_score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '累計スコア',
    //  `challenge_count` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(リセットなし)',
    //  `reset_challenge_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '挑戦回数(デイリーリセット対象)',
    //  `reset_ad_challenge_count` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '広告視聴での挑戦回数(デイリーリセット対象)',
    //  `max_received_max_score_reward` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '受取済みの最高スコア報酬の最高スコア数値',
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
            $table->string('received_rank_reward_group_id', 255)->nullable()->comment('mst_advent_battle_reward_groups.id')->after('max_received_max_score_reward');
        });
        Schema::dropIfExists('usr_advent_battle_rewards');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('usr_advent_battle_rewards', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_advent_battle_reward_group_id', 255)->comment('mst_advent_battle_reward_groups.id');
            $table->timestampsTz();
            $table->primary(['usr_user_id', 'mst_advent_battle_reward_group_id'], 'pk_usr_user_id_mst_advent_battle_reward_group_id');
        });
        Schema::table('usr_advent_battles', function (Blueprint $table) {
            $table->dropColumn('received_rank_reward_group_id');
        });
    }
};
