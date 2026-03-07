<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_mission_normals` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID',
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //     `mission_type` tinyint(3) unsigned NOT NULL COMMENT 'ミッションタイプのenum値',
    //     `mst_mission_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ミッションのマスタデータのID(mst_mission_xxxs.id)',
    //     `status` tinyint(3) unsigned NOT NULL COMMENT 'ミッションステータス',
    //     `progress` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT '進捗値',
    //     `next_reset_at` timestamp NULL DEFAULT NULL COMMENT '次回リセットする日時',
    //     `cleared_at` timestamp NULL DEFAULT NULL COMMENT '達成日時',
    //     `received_reward_at` timestamp NULL DEFAULT NULL COMMENT '報酬受取日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`usr_user_id`,`mission_type`,`mst_mission_id`) /*T![clustered_index] CLUSTERED */,
    //     KEY `idx_user_id_status` (`usr_user_id`,`status`),
    //     UNIQUE KEY `usr_mission_normals_id_unique` (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ノーマル系ミッションのユーザー進捗管理';

    // 列追加
    // status列の後：is_open	"unsigned tinyint not null default 0"	"開放ステータス 0：未開放 （報酬受取不可、バッジ数に含めない） 1：開放済"
    // progress列の後：unlock_progress	"unsigned bigint not null default 0"	開放進捗値

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_mission_normals', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_open')->nullable(false)->default(0)->comment('開放ステータス 0:未開放,1:開放済')->after('status');
            $table->unsignedBigInteger('unlock_progress')->nullable(false)->default(0)->comment('開放進捗値')->after('progress');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_mission_normals', function (Blueprint $table) {
            $table->dropColumn('is_open');
            $table->dropColumn('unlock_progress');
        });
    }
};
