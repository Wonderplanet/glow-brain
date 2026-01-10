<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_artworks_i18n` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `mst_artwork_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_series.id',
    //     `language` enum('ja') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '言語',
    //     `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '原画名',
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     PRIMARY KEY (`id`),
    //     UNIQUE KEY `uk_mst_artwork_id_language` (`mst_artwork_id`,`language`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画名などの設定';

    // 変更内容
    // - name: name
    // type: string
    // - name: description (追加)
    // type: string

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_artworks_i18n', function (Blueprint $table) {
            $table->string('description')->default('')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_artworks_i18n', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
