<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_mission_events` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //     `mst_mission_event_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_mission_events.id',
    //     `status` tinyint NOT NULL COMMENT '0: 未クリア 1: クリア 2: 報酬受取済',
    //     `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //     `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     UNIQUE KEY `usr_user_id_mst_mission_event_id_unique` (`usr_user_id`,`mst_mission_event_id`),
    //     KEY `usr_user_id_status_index` (`usr_user_id`,`status`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 既存テーブルは削除
        Schema::dropIfExists('usr_mission_events');

        // 新規設定でテーブル作成
        Schema::create('usr_mission_events', function (Blueprint $table) {
            $table->string('id', 255)->unique();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->tinyInteger('mission_type')->comment('ミッションタイプ');
            $table->string('mst_mission_id', 255)->comment('イベントミッションのマスタデータのID');
            $table->tinyInteger('status')->default(0)->comment('ステータス');
            $table->tinyInteger('is_open')->default(0)->comment('開放ステータス');
            $table->bigInteger('progress')->default(0)->comment('進捗値');
            $table->bigInteger('unlock_progress')->default(0)->comment('開放進捗値');
            $table->timestampTz('next_reset_at')->nullable()->comment('次回リセットする日時');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();

            $table->index(['usr_user_id', 'status'], 'idx_user_id_status');

            $table->primary(['usr_user_id', 'mission_type', 'mst_mission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_mission_events');

        // 既存テーブルを再作成
        Schema::create('usr_mission_events', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('mst_mission_event_id', 255)->comment('mst_mission_events.id');
            $table->tinyInteger('status')->comment('0: 未クリア 1: クリア 2: 報酬受取済');
            $table->timestampTz('cleared_at')->nullable()->comment('達成日時');
            $table->timestampTz('received_reward_at')->nullable()->comment('報酬受取日時');
            $table->timestampsTz();
            $table->unique(['usr_user_id', 'mst_mission_event_id'], 'usr_user_id_mst_mission_event_id_unique');
            $table->index(['usr_user_id', 'status'], 'usr_user_id_status_index');
        });
    }
};
