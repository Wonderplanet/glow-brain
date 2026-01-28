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
        // usr
        // usr_store_product_historiesにageを追加
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->integer('age')->comment('年齢')->after('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // usr
        // usr_store_product_historiesからageを削除
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropColumn('age');
        });
    }
};
