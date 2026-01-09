<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // CREATE TABLE `adm_message_distribution_inputs` (
    //     `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    //     `create_status` enum('Editing','Pending','Approved') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `start_at` timestamp NOT NULL,
    //     `expired_at` timestamp NULL DEFAULT NULL,
    //     `mng_message_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `opr_messages_txt` text COLLATE utf8mb4_unicode_ci,
    //     `opr_message_distributions_txt` text COLLATE utf8mb4_unicode_ci,
    //     `opr_message_i18ns_txt` text COLLATE utf8mb4_unicode_ci,
    //     `target_type` enum('All','UserId','MyId') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `target_ids_txt` longtext COLLATE utf8mb4_unicode_ci,
    //     `display_target_id_input_type` enum('All','Input','Csv') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All',
    //     `account_created_type` enum('Unset','Started','Ended','Both') COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            // カラム名の変更
            $table->renameColumn('opr_message_id', 'mng_message_id');
            // テキストカラム名の変更
            $table->renameColumn('opr_messages_txt', 'mng_messages_txt');
            $table->renameColumn('opr_message_distributions_txt', 'mng_message_distributions_txt');
            $table->renameColumn('opr_message_i18ns_txt', 'mng_message_i18ns_txt');

            // 列コメント追加
            $table->string('mng_message_id')->nullable()->comment('mng_messages.id')->change();
            $table->text('mng_messages_txt')->comment('mng_messagesレコードのシリアライズデータ')->change();
            $table->text('mng_message_distributions_txt')->comment('mng_message_rewardsレコードのシリアライズデータ')->change();
            $table->text('mng_message_i18ns_txt')->comment('mng_messages_i18nレコードのシリアライズデータ')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adm_message_distribution_inputs', function (Blueprint $table) {
            // カラム名を元に戻す
            $table->renameColumn('mng_message_id', 'opr_message_id');
            $table->renameColumn('mng_messages_txt', 'opr_messages_txt');
            $table->renameColumn('mng_message_distributions_txt', 'opr_message_distributions_txt');
            $table->renameColumn('mng_message_i18ns_txt', 'opr_message_i18ns_txt');
        });
    }
};

