<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_user_logins` (
    //  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //  `first_login_at` timestamp NOT NULL COMMENT '初回ログイン日時',
    //  `last_login_at` timestamp NOT NULL COMMENT '最終ログイン日時',
    //  `login_count` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ログイン回数',
    //  `login_day_count` int(11) NOT NULL DEFAULT '0' COMMENT '生涯累計のログイン日数',
    //  `login_continue_day_count` int(11) NOT NULL DEFAULT '0' COMMENT '連続ログイン日数。連続ログインが途切れたら0にリセットする。',
    //  `comeback_day_count` int(11) NOT NULL DEFAULT '0' COMMENT '最終ログインからの復帰にかかった日数',
    //  `created_at` timestamp NULL DEFAULT NULL,
    //  `updated_at` timestamp NULL DEFAULT NULL,
    //  PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //  UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_user_logins', function (Blueprint $table) {
            $table->timestampTz('hourly_accessed_at')->comment('1時間毎の最初のアクセス日時')->after('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_user_logins', function (Blueprint $table) {
            $table->dropColumn('hourly_accessed_at');
        });
    }
};
