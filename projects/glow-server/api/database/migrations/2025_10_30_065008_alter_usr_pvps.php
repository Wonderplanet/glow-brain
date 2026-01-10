<?php

use App\Traits\MigrationAddColumnCommentsTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use MigrationAddColumnCommentsTrait;

    // 変更前
    //CREATE TABLE `usr_pvps` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //  `sys_pvp_season_id` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sys_pvp_seasons.id',
    //  `score` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
    //  `pvp_rank_class_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'PVPランク区分',
    //  `pvp_rank_class_level` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'PVPランク区分レベル',
    //  `ranking` int(11) DEFAULT NULL COMMENT 'ランキング',
    //  `is_season_reward_received` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'シーズン報酬受け取り済みか',
    //  `is_excluded_ranking` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ランキングから除外されているか',
    //  `daily_remaining_challenge_count` int(10) unsigned NOT NULL COMMENT '残りアイテム消費なし挑戦可能回数',
    //  `daily_remaining_item_challenge_count` int(10) unsigned NOT NULL COMMENT '残りアイテム消費あり挑戦可能回数',
    //  `last_played_at` timestamp NULL DEFAULT NULL COMMENT '最終プレイ日時',
    //  `latest_reset_at` timestamp NOT NULL COMMENT 'リセット日時',
    //  `selected_opponent_candidates` json DEFAULT NULL COMMENT '選択した対戦相手の情報リスト',
    //  `created_at` timestamp NULL DEFAULT NULL,
    //  `updated_at` timestamp NULL DEFAULT NULL,
    //  PRIMARY KEY (`usr_user_id`,`sys_pvp_season_id`) /*T![clustered_index] CLUSTERED */
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='開催毎の個人PVP情報'

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->unsignedBigInteger('score')->default(0)->comment('スコア')->change();
            $table->unsignedBigInteger('max_received_score_reward')->default(0)->comment('受取済みの最高スコア報酬のスコア数値')->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_pvps', function (Blueprint $table) {
            $table->unsignedBigInteger('score')->default(0)->comment('パーティ番号')->change();
            $table->dropColumn('max_received_score_reward');
        });
    }
};
