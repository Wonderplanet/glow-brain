<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //CREATE TABLE `mst_artwork_fragments` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `mst_artwork_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_artworks.id',
    //  `drop_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ステージのドロップ単位(非ドロップはNULL)',
    //  `drop_percentage` smallint unsigned DEFAULT NULL COMMENT 'ドロップ率(非ドロップはNULL)',
    //  `rarity` enum('N','R','SR','SSR') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
    //  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  PRIMARY KEY (`id`)
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='原画のかけら設定';

    // 変更内容
    // -     - name: assetKey
    // -       type: string
    // +     - name: assetNum
    // +       type: int

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_artwork_fragments', function (Blueprint $table) {
            $table->dropColumn('asset_key');
            $table->integer('asset_num')->default(0)->after('rarity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_artwork_fragments', function (Blueprint $table) {
            $table->dropColumn('asset_num');
            $table->string('asset_key')->after('rarity');
        });
    }
};
