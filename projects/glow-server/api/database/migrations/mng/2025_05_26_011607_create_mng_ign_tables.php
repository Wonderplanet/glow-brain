<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

// CREATE TABLE `mng_in_game_notices` (
//   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
//   `adm_promotion_tag_id` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '昇格タグID(adm_promotion_tags.id)',
//   `display_type` enum('BasicBanner','Dialog') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '表示モード',
//   `enable` tinyint unsigned NOT NULL COMMENT '有効フラグ',
//   `priority` int unsigned NOT NULL COMMENT '表示優先度',
//   `display_frequency_type` enum('Always','Daily','Weekly','Monthly','Once') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '表示頻度タイプ',
//   `destination_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先タイプ',
//   `destination_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先情報',
//   `destination_path_detail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '遷移先詳細情報',
//   `start_at` timestamp NOT NULL COMMENT '掲載開始日時',
//   `end_at` timestamp NOT NULL COMMENT '掲載終了日時',
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

// -- Create syntax for TABLE 'mng_in_game_notices_i18n'
// CREATE TABLE `mng_in_game_notices_i18n` (
//   `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
//   `release_key` bigint NOT NULL DEFAULT '1',
//   `mng_in_game_notice_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'mng_in_game_notices.id',
//   `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語',
//   `title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'タイトル',
//   `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT '本文テキスト',
//   `banner_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'バナーURL',
//   `button_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'ボタンに表示するテキスト',
//   `created_at` timestamp NULL DEFAULT NULL,
//   `updated_at` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
// ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mng_in_game_notices', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            $table->string('adm_promotion_tag_id', 255)->nullable()->comment('昇格タグID(adm_promotion_tags.id)');
            $table->enum('display_type', ['BasicBanner', 'Dialog'])->comment('表示モード');
            $table->unsignedTinyInteger('enable')->comment('有効フラグ');
            $table->unsignedInteger('priority')->comment('表示優先度');
            $table->enum('display_frequency_type', ['Always', 'Daily', 'Weekly', 'Monthly', 'Once'])->comment('表示頻度タイプ');
            $table->string('destination_type', 255)->comment('遷移先タイプ');
            $table->string('destination_path', 255)->comment('遷移先情報');
            $table->string('destination_path_detail', 255)->comment('遷移先詳細情報');
            $table->timestampTz('start_at')->comment('掲載開始日時');
            $table->timestampTz('end_at')->comment('掲載終了日時');
            $table->timestampsTz();
        });

        Schema::create('mng_in_game_notices_i18n', function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('ID');
            // mngにリリースキーは不要
            $table->string('mng_in_game_notice_id', 255)->comment('mng_in_game_notices.id');
            $table->enum('language', ['ja'])->default('ja')->comment('言語');
            $table->text('title')->nullable()->comment('タイトル');
            $table->text('description')->comment('本文テキスト');
            $table->string('banner_url', 255)->comment('バナーURL');
            $table->string('button_title', 255)->comment('ボタンに表示するテキスト');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mng_in_game_notices_i18n');
        Schema::dropIfExists('mng_in_game_notices');
    }
};
