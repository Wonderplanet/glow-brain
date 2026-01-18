<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_mission_statuses` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'usr_users.id',
    //     `beginner_mission_status` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '初心者ミッション未クリア: 0 初心者ミッションクリア: 1',
    //     `mission_unlocked_at` timestamp NULL DEFAULT NULL COMMENT 'ミッション解放日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     UNIQUE KEY `uk_usr_user_id` (`usr_user_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // beginner_mission_status列の後に、latest_mst_hash列(varchar(255) not null)を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_mission_statuses', function (Blueprint $table) {
            $table->string('latest_mst_hash', 255)->after('beginner_mission_status')->comment('前回即時判定をしたときのマスタデータハッシュ値');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_mission_statuses', function (Blueprint $table) {
            $table->dropColumn('latest_mst_hash');
        });
    }
};
