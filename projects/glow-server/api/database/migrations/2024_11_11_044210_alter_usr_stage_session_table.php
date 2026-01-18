<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_stage_sessions` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `is_valid` tinyint(3) unsigned NOT NULL DEFAULT '0',
    //  `party_no` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'パーティ番号',
    //  `continue_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'コンティニュー回数',
    //  `opr_campaign_ids` json DEFAULT NULL COMMENT 'opr_campaigns.idの配列',
    //  `created_at` timestamp NULL DEFAULT NULL,
    //  `updated_at` timestamp NULL DEFAULT NULL,
    //  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //  UNIQUE KEY `usr_stage_sessions_usr_user_id_unique` (`usr_user_id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    // 変更内容
    // continue_count列の後に、daily_continue_ad_count列(int(10) unsigned not null)を追加
    // opr_campaign_ids列の後に、latest_reset_at列(timestamp not null)を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->integer('daily_continue_ad_count')->unsigned()->default(0)->comment('1日の広告コンティニュー回数')->after('continue_count');
            $table->timestampTz('latest_reset_at')->comment('リセット日時')->after('opr_campaign_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stage_sessions', function (Blueprint $table) {
            $table->dropColumn('daily_continue_ad_count');
            $table->dropColumn('latest_reset_at');
        });
    }
};
