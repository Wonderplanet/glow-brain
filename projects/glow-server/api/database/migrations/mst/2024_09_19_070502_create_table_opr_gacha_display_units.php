<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `opr_gachas_i18n` (
    //     `id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
    //     `opr_gacha_id` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'opr_gachas.id',
    //     `language` enum('ja','en','zh-Hant') COLLATE utf8mb4_bin NOT NULL DEFAULT 'ja' COMMENT '言語情報',
    //     `name` text COLLATE utf8mb4_bin COMMENT 'ガチャ名',
    //     `description` text COLLATE utf8mb4_bin COMMENT 'ガチャ説明',
    //     `max_rarity_upper_description` varchar(255) COLLATE utf8mb4_bin DEFAULT '' COMMENT '最高レアリティ天井の文言',
    //     `pickup_upper_description` varchar(255) COLLATE utf8mb4_bin DEFAULT '' COMMENT 'ピックアップ天井の文言',
    //     `banner_url` text COLLATE utf8mb4_bin COMMENT 'バナーURL',
    //     `logo_banner_url` text COLLATE utf8mb4_bin COMMENT '詳細へ飛んだ後のロゴバナーurl',
    //     `background_url` text COLLATE utf8mb4_bin COMMENT '背景URL',
    //     `gacha_background_color` varchar(255) COLLATE utf8mb4_bin NOT NULL COMMENT 'ガチャ背景色',
    //     `gacha_banner_size` enum('SizeM','SizeL') COLLATE utf8mb4_bin NOT NULL DEFAULT 'SizeM' COMMENT 'ガチャバナーサイズ',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `created_at` timestamp NULL DEFAULT NULL,
    //     `updated_at` timestamp NULL DEFAULT NULL,
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `opr_gacha_id_unique` (`opr_gacha_id`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('opr_gacha_display_units', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->bigInteger('release_key')->default(1);
            $table->string('opr_gacha_id')->default('');
            $table->string('mst_unit_id')->default('');
            $table->integer('sort_order');
        });

        // opr_gachas_i18nのbackground_urlを削除
        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->dropColumn('background_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opr_gacha_display_units');

        Schema::table('opr_gachas_i18n', function (Blueprint $table) {
            $table->text('background_url')->comment('背景URL');
        });
    }
};
