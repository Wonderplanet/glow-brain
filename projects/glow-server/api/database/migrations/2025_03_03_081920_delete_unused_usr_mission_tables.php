<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    //     -- Create syntax for TABLE 'usr_mission_achievement_progresses'
    // CREATE TABLE `usr_mission_achievement_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー「criterion_type:criterion_value」',
    //   `progress` bigint unsigned NOT NULL COMMENT '生涯累積進捗値',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_achievement_progresses_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_beginner_progresses'
    // CREATE TABLE `usr_mission_beginner_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT '初心者ミッション累積進捗値',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_beginner_progresses_id_unique` (`id`),
    //   KEY `usr_mission_beginner_progresses_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_daily_progresses'
    // CREATE TABLE `usr_mission_daily_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'デイリー累積進捗値',
    //   `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_daily_progresses_id_unique` (`id`),
    //   KEY `usr_mission_daily_progresses_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_event_daily_progresses'
    // CREATE TABLE `usr_mission_event_daily_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint NOT NULL DEFAULT '0' COMMENT '生涯累積進捗値',
    //   `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_event_daily_progresses_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_event_progresses'
    // CREATE TABLE `usr_mission_event_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint NOT NULL DEFAULT '0' COMMENT 'デイリー累積進捗値',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_event_progresses_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_limited_term_progresses'
    // CREATE TABLE `usr_mission_limited_term_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint NOT NULL DEFAULT '0' COMMENT '進捗値',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_weekly_progresses'
    // CREATE TABLE `usr_mission_weekly_progresses` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `criterion_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '条件キー',
    //   `progress` bigint unsigned NOT NULL DEFAULT '0' COMMENT 'ウィークリー累積進捗値',
    //   `latest_update_at` timestamp NOT NULL COMMENT '週跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`criterion_key`) /*T![clustered_index] CLUSTERED */,
    //   UNIQUE KEY `usr_mission_weekly_progresses_id_unique` (`id`),
    //   KEY `usr_mission_weekly_progresses_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



    // -- Create syntax for TABLE 'usr_mission_achievements'
    // CREATE TABLE `usr_mission_achievements` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `mst_mission_achievement_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_achievements.id',
    //   `status` tinyint unsigned NOT NULL COMMENT '0:未クリア, 1:クリア, 2:報酬受取済',
    //   `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //   `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`mst_mission_achievement_id`) /*T![clustered_index] CLUSTERED */,
    //   KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
    //   UNIQUE KEY `usr_mission_achievements_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_beginners'
    // CREATE TABLE `usr_mission_beginners` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `mst_mission_beginner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_beginners.id',
    //   `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
    //   `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //   `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`mst_mission_beginner_id`) /*T![clustered_index] CLUSTERED */,
    //   KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
    //   UNIQUE KEY `usr_mission_beginners_id_unique` (`id`),
    //   KEY `usr_mission_beginners_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_dailies'
    // CREATE TABLE `usr_mission_dailies` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `mst_mission_daily_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_dailies.id',
    //   `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
    //   `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //   `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //   `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`mst_mission_daily_id`) /*T![clustered_index] CLUSTERED */,
    //   KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
    //   UNIQUE KEY `usr_mission_dailies_id_unique` (`id`),
    //   KEY `usr_mission_dailies_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_event_dailies'
    // CREATE TABLE `usr_mission_event_dailies` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `mst_mission_event_daily_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_event_dailies.id',
    //   `status` tinyint NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
    //   `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //   `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //   `latest_update_at` timestamp NOT NULL COMMENT '日跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`mst_mission_event_daily_id`) /*T![clustered_index] CLUSTERED */,
    //   KEY `usr_user_id_status_index` (`usr_user_id`,`status`),
    //   UNIQUE KEY `usr_mission_event_dailies_id_unique` (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // -- Create syntax for TABLE 'usr_mission_weeklies'
    // CREATE TABLE `usr_mission_weeklies` (
    //   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //   `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //   `mst_mission_weekly_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_weeklies.id',
    //   `status` int unsigned NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
    //   `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //   `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //   `latest_update_at` timestamp NOT NULL COMMENT '週跨ぎリセット判定用。ステータス変更をした最終更新日時',
    //   `created_at` timestamp NULL DEFAULT NULL,
    //   `updated_at` timestamp NULL DEFAULT NULL,
    //   PRIMARY KEY (`usr_user_id`,`mst_mission_weekly_id`) /*T![clustered_index] CLUSTERED */,
    //   KEY `idx_usr_user_id_status` (`usr_user_id`,`status`),
    //   UNIQUE KEY `usr_mission_weeklies_id_unique` (`id`),
    //   KEY `usr_mission_weeklies_usr_user_id_index` (`usr_user_id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // CREATE TABLE `usr_mission_recent_additions` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //     `mission_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションタイプ',
    //     `latest_release_key` int NOT NULL COMMENT '判定済みの中で最新のリリースキー',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`usr_user_id`,`mission_type`) /*T![clustered_index] CLUSTERED */,
    //     UNIQUE KEY `usr_mission_recent_additions_id_unique` (`id`),
    //     KEY `usr_mission_recent_additions_usr_user_id_index` (`usr_user_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // テーブルを削除
        Schema::dropIfExists('usr_mission_achievement_progresses');
        Schema::dropIfExists('usr_mission_beginner_progresses');
        Schema::dropIfExists('usr_mission_daily_progresses');
        Schema::dropIfExists('usr_mission_event_daily_progresses');
        Schema::dropIfExists('usr_mission_event_progresses');
        Schema::dropIfExists('usr_mission_limited_term_progresses');
        Schema::dropIfExists('usr_mission_weekly_progresses');

        Schema::dropIfExists('usr_mission_achievements');
        Schema::dropIfExists('usr_mission_beginners');
        Schema::dropIfExists('usr_mission_dailies');
        Schema::dropIfExists('usr_mission_event_dailies');
        Schema::dropIfExists('usr_mission_weeklies');

        Schema::dropIfExists('usr_mission_recent_additions');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // テーブルを作成
        Schema::create('usr_mission_achievement_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー「criterion_type:criterion_value」');
            $table->unsignedBigInteger('progress')->comment('生涯累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_beginner_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('初心者ミッション累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_event_daily_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('生涯累積進捗値');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_event_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('デイリー累積進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_limited_term_progresses', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->bigInteger('progress')->default(0)->comment('進捗値');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });

        Schema::create('usr_mission_weekly_progresses', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('criterion_key', 255)->comment('条件キー');
            $table->unsignedBigInteger('progress')->default(0)->comment('ウィークリー累積進捗値');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'criterion_key']);
        });



        Schema::create('usr_mission_achievements', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_achievement_id', 255)->comment('mst_mission_achievements.id');
            $table->unsignedTinyInteger('status')->comment('0:未クリア, 1:クリア, 2:報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_achievement_id'], 'uk_usr_user_id_mst_mission_achievement_id');
        });

        Schema::create('usr_mission_beginners', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_beginner_id', 255)->comment('mst_mission_beginners.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->primary(['usr_user_id', 'mst_mission_beginner_id']);
        });

        Schema::create('usr_mission_dailies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_daily_id', 255)->comment('mst_mission_dailies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_daily_id'], 'uk_usr_user_id_mst_mission_daily_id');
        });

        Schema::create('usr_mission_event_dailies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_daily_id', 255)->comment('mst_mission_event_dailies.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('日跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
            $table->unique(['usr_user_id', 'mst_mission_event_daily_id'], 'usr_user_id_mst_mission_event_daily_id_unique');
        });

        Schema::create('usr_mission_weeklies', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mst_mission_weekly_id', 255)->comment('mst_mission_weeklies.id');
            $table->unsignedInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampTz('latest_update_at')->comment('週跨ぎリセット判定用。ステータス変更をした最終更新日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_usr_user_id_status');
            $table->unique(['usr_user_id', 'mst_mission_weekly_id'], 'uk_usr_user_id_mst_mission_weekly_id');
        });


        Schema::create('usr_mission_recent_additions', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->index()->comment('usr_users.id');
            $table->string('mission_type', 255)->comment('ミッションタイプ');
            $table->integer('latest_release_key')->comment('判定済みの中で最新のリリースキー');
            $table->timestampsTz();

            $table->primary(['usr_user_id', 'mission_type']);
        });
    }
};
