<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
//     CREATE TABLE `adm_in_game_notices` (
//   `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
//   `opr_in_game_notice_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'opr_in_game_notices.id',
//   `status` enum('InProgress','PendingApproval','Reject','Approved','Active','Withdrawn') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ステータス',
//   `author_adm_user_id` bigint NOT NULL COMMENT '作成者ユーザーID',
//   `approval_adm_user_id` bigint DEFAULT NULL COMMENT '承認者ユーザーID',
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

//opr_in_game_notice_idをmng_in_game_notice_idへ改名と、コメントをopr_in_game_notices.idからmng_in_game_notices.idへ変更するマイグレーション

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adm_in_game_notices', function (Blueprint $table) {
            // renameと列コメント変更をalterで同時に行う
            $table->string('mng_in_game_notice_id', 255)
                ->comment('mng_in_game_notices.id')
                ->after('id');
            $table->dropColumn('opr_in_game_notice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_in_game_notices', function (Blueprint $table) {
            // renameと列コメント変更をalterで同時に行う
            $table->string('opr_in_game_notice_id', 255)
                ->comment('opr_in_game_notices.id')
                ->after('id');
            $table->dropColumn('mng_in_game_notice_id');
        });
    }
};
