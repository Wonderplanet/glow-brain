<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CREATE TABLE `usr_users` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `status` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'ユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN',
    //     `tutorial_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'チュートリアルステータス',
    //     `tos_version` smallint unsigned NOT NULL DEFAULT '0',
    //     `privacy_policy_version` smallint unsigned NOT NULL DEFAULT '0',
    //     `bn_user_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'BNIDユーザーID',
    //     `is_account_linking_restricted` tinyint unsigned NOT NULL DEFAULT '0' COMMENT 'マルチログイン制限フラグ',
    //     `client_uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'クライアントUUID',
    //     `suspend_end_at` timestamp NULL DEFAULT NULL COMMENT '利用停止状態の終了日時',
    //     `game_start_at` timestamp NULL DEFAULT NULL COMMENT 'ゲーム開始日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     KEY `bn_user_id_index` (`bn_user_id`),
    //     KEY `client_uuid_created_at_index` (`client_uuid`,`created_at`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('global_consent_version')
                ->default(0)
                ->after('privacy_policy_version')
                ->comment('グローバルコンセントバージョン');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->dropColumn('global_consent_version');
        });
    }
};
