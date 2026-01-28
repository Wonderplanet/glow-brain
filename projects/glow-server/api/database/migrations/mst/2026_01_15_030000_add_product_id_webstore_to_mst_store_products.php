<?php

use App\Domain\Constants\Database;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = Database::MST_CONNECTION;

    /**
     * Run the migrations.
     *
     * WebStore対応のため以下を実施:
     * 1. mst_store_productsにWebStore用のproduct_id_webstoreカラムを追加し、
     *    既存のproduct_id_ios/androidをNULL許容に変更
     * 2. mst_store_products_i18nにprice_webstoreカラムを追加
     *    （W2決済事前確認で有料/無料判定に使用）
     */
    public function up(): void
    {
        // mst_store_products
        Schema::table('mst_store_products', function (Blueprint $table) {
            // nullableに変更
            $table->string('product_id_ios', 255)->nullable()->comment('AppStoreのプロダクトID')->change();
            $table->string('product_id_android', 255)->nullable()->comment('GooglePlayのプロダクトID')->change();
            // WebStore用カラム追加
            $table->string('product_id_webstore', 255)->nullable()->comment('WebStoreのSKU（空文字: モバイルアプリ専用商品）')->after('product_id_android');
            $table->index('product_id_webstore', 'idx_product_id_webstore');
        });

        // mst_store_products_i18n
        Schema::table('mst_store_products_i18n', function (Blueprint $table) {
            $table->decimal('price_webstore', 10, 3)->nullable()->comment('WebStoreでの販売価格（NULL: WebStore非対応）')->after('price_android');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst_store_products_i18n
        Schema::table('mst_store_products_i18n', function (Blueprint $table) {
            $table->dropColumn('price_webstore');
        });

        // mst_store_products
        Schema::table('mst_store_products', function (Blueprint $table) {
            $table->dropIndex('idx_product_id_webstore');
            $table->dropColumn('product_id_webstore');
        });

        // 注意: product_id_ios/androidのNOT NULL制約は戻さない
        // (既存データがNULLになっている可能性があるため)
    }
};
