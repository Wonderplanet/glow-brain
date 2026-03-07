<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_stages` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_stage_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `clear_status` tinyint NOT NULL,
    //     `clear_count` bigint NOT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     UNIQUE KEY `usr_stages_usr_user_id_mst_stage_id_unique` (`usr_user_id`,`mst_stage_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // clear_countの後に、`clear_time_ms` int unsigned DEFAULT NULL COMMENT 'クリアタイム(ミリ秒)'を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->unsignedInteger('clear_time_ms')->nullable()->comment('クリアタイム(ミリ秒)')->after('clear_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_stages', function (Blueprint $table) {
            $table->dropColumn('clear_time_ms');
        });
    }
};
