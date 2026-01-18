<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // vipポイントの実装追加
        // usr
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->bigInteger('total_vip_point')->default(0)->comment('商品購入時に獲得したVIPポイントの合計')->after('renotify_at');
        });

        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント')->after('price_per_amount');
        });

        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント')->after('price_per_amount');
        });
        
        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント')->after('price_per_amount');
        });

        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント')->after('price_per_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // usr 
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->dropColumn('total_vip_point');
        });

        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('vip_point');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropColumn('vip_point');
        });
        
        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('vip_point');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('vip_point');
        });
    }
};
