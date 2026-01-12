<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CREATE TABLE `usr_mission_events` (
    // `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    // `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    // `mission_type` tinyint NOT NULL COMMENT 'ミッションタイプ',
    // `mst_mission_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'イベントミッションのマスタデータのID',
    // `mst_event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_events.id',
    // `status` tinyint NOT NULL DEFAULT '0' COMMENT 'ステータス',
    // `is_open` tinyint NOT NULL DEFAULT '0' COMMENT '開放ステータス',
    // `progress` bigint NOT NULL DEFAULT '0' COMMENT '進捗値',
    // `unlock_progress` bigint NOT NULL DEFAULT '0' COMMENT '開放進捗値',
    // `next_reset_at` timestamp NULL DEFAULT NULL COMMENT '次回リセットする日時',
    // `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    // `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    // `created_at` timestamp NULL DEFAULT NULL,
    // `updated_at` timestamp NULL DEFAULT NULL,
    // PRIMARY KEY (`usr_user_id`,`mission_type`,`mst_mission_id`) /*T![clustered_index] CLUSTERED */,
    // KEY `idx_usr_user_id_mst_event_id` (`usr_user_id`,`mst_event_id`),
    // KEY `idx_user_id_status` (`usr_user_id`,`status`),
    // UNIQUE KEY `usr_mission_events_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `usr_mission_limited_terms` (
    // `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    // `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    // `mst_mission_limited_term_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_limited_terms.id',
    // `status` tinyint NOT NULL DEFAULT '0' COMMENT 'ステータス',
    // `is_open` tinyint NOT NULL DEFAULT '0' COMMENT '開放ステータス',
    // `progress` bigint NOT NULL DEFAULT '0' COMMENT '進捗値',
    // `next_reset_at` timestamp NULL DEFAULT NULL COMMENT '次回リセットする日時',
    // `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    // `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    // `created_at` timestamp NULL DEFAULT NULL,
    // `updated_at` timestamp NULL DEFAULT NULL,
    // PRIMARY KEY (`usr_user_id`,`mst_mission_limited_term_id`) /*T![clustered_index] CLUSTERED */,
    // KEY `idx_user_id_status` (`usr_user_id`,`status`),
    // UNIQUE KEY `usr_mission_limited_terms_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `usr_mission_normals` (
    // `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
    // `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    // `mission_type` tinyint unsigned NOT NULL COMMENT 'ミッションタイプのenum値',
    // `mst_mission_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションのマスタデータのID(mst_mission_xxxs.id)',
    // `status` tinyint unsigned NOT NULL COMMENT 'ミッションステータス',
    // `is_open` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '開放ステータス 0:未開放,1:開放済',
    // `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '進捗値',
    // `unlock_progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '開放進捗値',
    // `next_reset_at` timestamp NULL DEFAULT NULL COMMENT '次回リセットする日時',
    // `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    // `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    // `created_at` timestamp NULL DEFAULT NULL,
    // `updated_at` timestamp NULL DEFAULT NULL,
    // PRIMARY KEY (`usr_user_id`,`mission_type`,`mst_mission_id`) /*T![clustered_index] CLUSTERED */,
    // KEY `idx_user_id_status` (`usr_user_id`,`status`),
    // UNIQUE KEY `usr_mission_normals_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ノーマル系ミッションのユーザー進捗管理';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 各テーブルのnext_reset_atの後に、latest_reset_at列をtimestamptzでnot nullで追加
        // その後、next_reset_at列を削除

        Schema::table('usr_mission_events', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->nullable(false)->after('next_reset_at')->comment('最終リセット日時');

            $table->dropColumn('next_reset_at');
        });

        Schema::table('usr_mission_limited_terms', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->nullable(false)->after('next_reset_at')->comment('最終リセット日時');

            $table->dropColumn('next_reset_at');
        });

        Schema::table('usr_mission_normals', function (Blueprint $table) {
            $table->timestampTz('latest_reset_at')->nullable(false)->after('next_reset_at')->comment('最終リセット日時');

            $table->dropColumn('next_reset_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_mission_events', function (Blueprint $table) {
            $table->timestampTz('next_reset_at')->nullable()->after('latest_reset_at')->comment('次回リセットする日時');

            $table->dropColumn('latest_reset_at');
        });

        Schema::table('usr_mission_limited_terms', function (Blueprint $table) {
            $table->timestampTz('next_reset_at')->nullable()->after('latest_reset_at')->comment('次回リセットする日時');

            $table->dropColumn('latest_reset_at');
        });

        Schema::table('usr_mission_normals', function (Blueprint $table) {
            $table->timestampTz('next_reset_at')->nullable()->after('latest_reset_at')->comment('次回リセットする日時');

            $table->dropColumn('latest_reset_at');
        });
    }
};
