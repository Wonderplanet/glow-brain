<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    // CREATE TABLE `mst_items` (
    //     `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','GachaTicket','Etc') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    //     `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `rarity` enum('N','R','SR','SSR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //     `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
    //     `sort_order` int NOT NULL DEFAULT '0',
    //     `start_date` timestamp NOT NULL,
    //     `end_date` timestamp NOT NULL,
    //     `release_key` bigint NOT NULL DEFAULT '1',
    //     `destination_opr_product_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    //     PRIMARY KEY (`id`),
    //     KEY `mst_items_item_type_index` (`type`)
    //   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    // 変更内容：mst_itemsにmst_series_idを追加
    // - name: assetKey
    //     type: string
    //   - name: mstSeriesId
    //     type: string
    //   - name: destinationOprProductId
    //     type: string

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_items', function (Blueprint $table) {
            $table->string('mst_series_id', 255)->default('')->after('effect_value')->comment('mst_series.id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_items', function (Blueprint $table) {
            $table->dropColumn('mst_series_id');
        });
    }
};
