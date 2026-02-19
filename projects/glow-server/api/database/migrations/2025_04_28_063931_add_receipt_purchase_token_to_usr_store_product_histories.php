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
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table
                ->string('receipt_purchase_token', 255)
                ->comment('レシート記載、ストアから送られてきた購入トークン')
                ->after('receipt_bundle_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropColumn('receipt_purchase_token');
        });
    }
};
