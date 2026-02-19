<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_users` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `status` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'ユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN',
    //     `tutorial_status` smallint unsigned NOT NULL DEFAULT '0',
    //     `tos_version` smallint unsigned NOT NULL DEFAULT '0',
    //     `privacy_policy_version` smallint unsigned NOT NULL DEFAULT '0',
    //     `bn_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'BNIDユーザーID',
    //     `suspend_end_at` timestamp NULL DEFAULT NULL COMMENT '利用停止状態の終了日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     KEY `bn_user_id_index` (`bn_user_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // usr_users.tutorial_statusをvarchar(255)に変更

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->string('tutorial_status', 255)->default('')->change()->comment('チュートリアルステータス');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->smallInteger('tutorial_status')->change()->default(0);
        });
    }
};
