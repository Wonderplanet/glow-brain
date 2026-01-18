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
        // mst
        // mst_store_products
        Schema::table(CurrencyDBUtility::getTableName('mst_store_products'), function (Blueprint $table) {
            // product_id_iosをvarchar(255)に変更
            $table->string('product_id_ios', 255)->comment('AppStoreのプロダクトID')->change();
            // product_id_androidをvarchar(255)に変更
            $table->string('product_id_android', 255)->comment('GooglePlayのプロダクトID')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // mst
        // mst_store_products
        Schema::table(CurrencyDBUtility::getTableName('mst_store_products'), function (Blueprint $table) {
            // product_id_iosをvarchar(100)に変更
            $table->string('product_id_ios', 100)->comment('AppStoreのプロダクトID')->change();
            // product_id_androidをvarchar(40)に変更
            $table->string('product_id_android', 40)->comment('GooglePlayのプロダクトID')->change();
        });
    }
};
