<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_users` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `status` smallint(5) unsigned NOT NULL DEFAULT '1',
    //     `tutorial_status` smallint(5) unsigned NOT NULL DEFAULT '0',
    //     `tos_version` smallint(5) unsigned NOT NULL DEFAULT '0',
    //     `privacy_policy_version` smallint(5) unsigned NOT NULL DEFAULT '0',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // statusのデフォルトを0に変更
        // 以下コメント
        // ユーザーステータス
        // 0: 通常プレイ可
        // 1: 時限BAN
        // 2: 永久BAN
    // usr_users	suspend_end_at	timestamp	TRUE	NULL	利用停止状態の終了日時	"非NULL かつ status=1の場合で、
    // 現在日時がsuspend_end_atより後なら、利用停止状態を解除(status=0へ更新)する"
    // privacy_policy_versionの後に追加する

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(0)->change()->comment('ユーザーステータス 0:通常プレイ可 1:時限BAN 2:永久BAN');
            $table->timestampTz('suspend_end_at')->nullable()->comment('利用停止状態の終了日時')->after('privacy_policy_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('status')->default(1)->change();
            $table->dropColumn('suspend_end_at');
        });
    }
};
