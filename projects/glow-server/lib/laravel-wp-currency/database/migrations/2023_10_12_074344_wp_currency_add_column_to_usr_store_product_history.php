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
     * @return void
     */
    public function up()
    {
        // usr
        // TiDBの仕様では、ALTER TABLE実行前のカラムのみ参照できるため、追加するカラムにafterを使用する場合はクエリを分割する

        // pkeyの変更
        //   receipt_unique_id、platformでPKEYにする
        //   ※TiDBではprimary keyを変更できないため、ここで行っていたreceipt_unique_idとplatformを主キーとする変更は、
        //     2023_09_05_033240_wp_currency_usr_create_currency_tables.php で行っている
        //     最終的には、receipt_unique_idとplatformにユニークキーを設定している

        // カラムの追加
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('platform_product_id', 255)->comment('プラットフォーム側で定義しているproduct_id')->after('opr_product_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('mst_store_product_id', 255)->comment('マスターテーブルのプロダクトID')->after('platform_product_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('currency_code', 16)->comment('クライアントから送られてきた通貨コード')->after('mst_store_product_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('receipt_bundle_id', 255)->comment('レシート記載、ストアから送られてきた商品のバンドルID')->after('currency_code');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->bigInteger('paid_amount')->comment('有償一次通貨の付与量')->after('receipt_bundle_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->bigInteger('free_amount')->comment('無償一次通貨の付与量')->after('paid_amount');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->decimal('purchase_price', 20, 6)->comment('ストアから送られてきた実際の購入価格')->after('free_amount');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->decimal('price_per_amount', 20, 8)->comment('単価')->after('purchase_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // usr
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            // カラムの削除
            $table->dropColumn('platform_product_id');
            $table->dropColumn('mst_store_product_id');
            $table->dropColumn('currency_code');
            $table->dropColumn('receipt_bundle_id');
            $table->dropColumn('paid_amount');
            $table->dropColumn('free_amount');
            $table->dropColumn('purchase_price');
            $table->dropColumn('price_per_amount');
        });
    }
};
