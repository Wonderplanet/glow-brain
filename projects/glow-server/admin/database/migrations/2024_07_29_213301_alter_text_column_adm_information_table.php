<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // 変更前
    // CREATE TABLE `adm_informations` (
    //     `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `enable` tinyint unsigned NOT NULL,
    //     `status` enum('InProgress','PendingApproval','Reject','Approved','Active','Withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ステータス',
    //     `banner_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `html` text COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `author_adm_user_id` bigint NOT NULL COMMENT '作成者ユーザーID',
    //     `approval_adm_user_id` bigint DEFAULT NULL COMMENT '承認者ユーザーID',
    //     `pre_notice_start_at` timestamp NOT NULL COMMENT '予告掲載開始日時',
    //     `start_at` timestamp NOT NULL COMMENT '開催期間開始日時',
    //     `end_at` timestamp NOT NULL COMMENT '開催期間終了日時',
    //     `post_notice_end_at` timestamp NOT NULL COMMENT '開催期間終了後の掲載終了日時',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // adm_informationsのhtml_json列(json型)をhtmlの後に追加

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->json('html_json')->comment('本文のhtmlのjsonデータ')->after('html');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_informations', function (Blueprint $table) {
            $table->dropColumn('html_json');
        });
    }
};
