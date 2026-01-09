<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Usr
        //  usr_store_product_historiesにサロゲートキーとしてIDを追加、pkeyにする
        //  ※TiDBではprimary keyを変更できないため、ここで行っていたidを主キーとする変更は、
        //    2023_09_05_033240_wp_currency_usr_create_currency_tables.php で行っている
        //  そのためこのマイグレーションでは、receipt_unique_idとbilling_platformにユニークキーを設定するのみ行う

        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->unique(['receipt_unique_id', 'billing_platform'], 'receipt_unique_id_billing_platform_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            // receipt_unique_idとbilling_platformのユニークキーを削除
            $table->dropUnique('receipt_unique_id_billing_platform_unique');
        });
    }
};
