<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //CREATE TABLE `mst_enemy_characters` (
    //  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //  `release_key` bigint NOT NULL DEFAULT '1',
    //  `mst_series_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '作品ID',
    //  `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
    //) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容
    // - name: isDisplayedEncyclopedia
    //   type: bool

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->tinyInteger('is_displayed_encyclopedia')->default(0)->after('asset_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_enemy_characters', function (Blueprint $table) {
            $table->dropColumn('is_displayed_encyclopedia');
        });
    }
};
