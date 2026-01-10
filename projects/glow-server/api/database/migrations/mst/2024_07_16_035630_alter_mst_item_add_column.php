<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    // 変更前
    //     CREATE TABLE `mst_items` (
    //         `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `type` enum('CharacterFragment','RankUpMaterial','StageMedal','IdleCoinBox','IdleRankUpMaterialBox','RandomFragmentBox','SelectionFragmentBox','Etc') COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `group_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `rarity` enum('N','R','SR','SSR') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `asset_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    //         `effect_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '特定item_typeのときの効果値',
    //         `sort_order` int NOT NULL DEFAULT '0',
    //         `start_date` timestamp NOT NULL,
    //         `end_date` timestamp NOT NULL,
    //         `release_key` bigint NOT NULL DEFAULT '1',
    //         PRIMARY KEY (`id`),
    //         KEY `mst_items_item_type_index` (`type`)
    //     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('mst_items', function (Blueprint $table) {
            $table->string('destination_opr_product_id', 255);
            $table->string('destination_banner_asset_key', 255);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('mst_items', function (Blueprint $table) {
            $table->dropColumn('destination_opr_product_id');
            $table->dropColumn('destination_banner_asset_key');
        });
    }
};
