<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `usr_user_profiles` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `usr_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `my_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
    //     `is_change_name` tinyint(3) unsigned NOT NULL DEFAULT '0',
    //     `mst_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'アバターとして設定したユニットのID',
    //     `mst_emblem_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'エンブレムID',
    //     `mst_avatar_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
    //     `mst_avatar_frame_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
    //     `name_update_at` timestamp NULL DEFAULT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`) /*T![clustered_index] CLUSTERED */,
    //     UNIQUE KEY `uk_usr_user_id` (`usr_user_id`),
    //     UNIQUE KEY `usr_user_profiles_my_id_unique` (`my_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // usr_user_profiles
    // is_change_nameの後に、birth_date(unsigned int nullable) を追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->text('birth_date')->after('is_change_name')->comment('暗号化された生年月日の数字データ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usr_user_profiles', function (Blueprint $table) {
            $table->dropColumn('birth_date');
        });
    }
};
