<?php

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
        // product_idのカラムコメントを修正
        // mst
        // mst_store_products
        Schema::table(CurrencyDBUtility::getTableName('mst_store_products'), function (Blueprint $table) {
            $table->string('product_id_ios', 100)->comment('AppStoreのプロダクトID')->change();
            $table->string('product_id_android', 40)->comment('GooglePlayのプロダクトID')->change();
        });

        // usr
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('product_id', 255)->comment('ストアのプロダクトID')->change();
        });

        // log
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('product_id', 255)->comment('ストアのプロダクトID')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // mst
        // mst_store_products
        Schema::table(CurrencyDBUtility::getTableName('mst_store_products'), function (Blueprint $table) {
            $table->string('product_id_ios', 100)->comment('AppStoreの商品ID')->change();
            $table->string('product_id_android', 40)->comment('GooglePlayの商品ID')->change();
        });

        // usr
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('product_id', 255)->comment('ストアの製品ID')->change();
        });

        // log
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('product_id', 255)->comment('ストアの製品ID')->change();
        });
    }
};
