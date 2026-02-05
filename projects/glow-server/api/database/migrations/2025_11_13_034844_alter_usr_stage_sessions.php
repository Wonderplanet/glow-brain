<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //変更前
    //CREATE TABLE `usr_stage_sessions` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_stages.id',
    //  `is_valid` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'ステージ挑戦中フラグ',
    //  `party_no` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
    //  `continue_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'コンティニュー回数',
    //  `daily_continue_ad_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1日の広告コンティニュー回数',
    //  `is_challenge_ad` tinyint(4) NOT NULL DEFAULT '0' COMMENT '広告視聴による挑戦か',
    //  `opr_campaign_ids` json DEFAULT NULL COMMENT 'opr_campaigns.idの配列',
    //  `latest_reset_at` timestamp NOT NULL COMMENT 'リセット日時',
    //  `created_at` timestamp NULL DEFAULT NULL COMMENT '作成日時のタイムスタンプ',
    //  `updated_at` timestamp NULL DEFAULT NULL COMMENT '更新日時のタイムスタンプ',
    //  PRIMARY KEY (`usr_user_id`) /*T![clustered_index] CLUSTERED */
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ステージのインゲームセッション管理'

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->unsignedInteger('auto_lap_count')->default(1)->comment('スタミナブースト周回指定')->after('opr_campaign_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->dropColumn('auto_lap_count');
        });
    }
};
