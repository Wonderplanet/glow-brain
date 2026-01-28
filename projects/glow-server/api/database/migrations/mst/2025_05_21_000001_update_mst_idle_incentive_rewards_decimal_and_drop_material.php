<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    protected $connection = Database::MST_CONNECTION;

    // CREATE TABLE `mst_idle_incentive_rewards` (
    // `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
    // `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
    // `mst_stage_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '報酬が変わるステージ進捗の閾値',
    // `base_coin_amount` decimal(10,2) NOT NULL COMMENT 'N分ごとのコインの基礎獲得数',
    // `base_exp_amount` decimal(10,2) NOT NULL COMMENT 'N分ごとの経験値の基礎獲得数',
    // `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_idle_incentive_items.mst_idle_incentive_item_group_id',
    // `base_rank_up_material_amount` decimal(10,2) NOT NULL DEFAULT '1.00' COMMENT 'リミテッドメモリーのベース獲得量',
    // PRIMARY KEY (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索報酬の基本設定';
    //
    // CREATE TABLE `mst_idle_incentive_items` (
    // `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID',
    // `release_key` bigint NOT NULL DEFAULT '1' COMMENT 'リリースキー',
    // `mst_idle_incentive_item_group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'グループID',
    // `mst_item_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'mst_items.id',
    // `base_amount` decimal(10,2) NOT NULL COMMENT 'ベース量',
    // PRIMARY KEY (`id`)
    // ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='探索報酬として配布したいアイテムをまとめて複数設定できるテーブル';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mst_idle_incentive_rewards', function (Blueprint $table) {
            $table->decimal('base_coin_amount', 6, 4)
                ->comment('N分ごとのコインの基礎獲得数')
                ->change();
            $table->decimal('base_exp_amount', 6, 4)
                ->comment('N分ごとの経験値の基礎獲得数')
                ->change();
            $table->dropColumn('base_rank_up_material_amount');
        });

        Schema::table('mst_idle_incentive_items', function (Blueprint $table) {
            $table->decimal('base_amount', 6, 4)
                ->comment('ベース量')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mst_idle_incentive_rewards', function (Blueprint $table) {
            $table->decimal('base_coin_amount', 10, 2)
                ->comment('N分ごとのコインの基礎獲得数')
                ->change();
            $table->decimal('base_exp_amount', 10, 2)
                ->comment('N分ごとの経験値の基礎獲得数')
                ->change();
            $table->decimal('base_rank_up_material_amount', 10, 2)
                ->default(1.00)
                ->comment('リミテッドメモリーのベース獲得量');
        });

        Schema::table('mst_idle_incentive_items', function (Blueprint $table) {
            $table->decimal('base_amount', 10, 2)
                ->comment('ベース量')
                ->change();
        });
    }
};
