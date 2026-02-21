<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WebStoreで1つの注文に複数アイテムが含まれる場合に対応するため、
     * order_idのユニーク制約を削除します。
     * receipt_unique_idに連番を付与することで一意性を保証します。
     *
     * 例:
     * - 1つ目のアイテム: receipt_unique_id="12345", order_id=12345
     * - 2つ目のアイテム: receipt_unique_id="12345_2", order_id=12345
     * - 3つ目のアイテム: receipt_unique_id="12345_3", order_id=12345
     */
    public function up(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropUnique('idx_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック時はorder_idのユニーク制約を復元
        // ただし、既に複数アイテムのデータが存在する場合は失敗する
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->unique('order_id', 'idx_order_id');
        });
    }
};
