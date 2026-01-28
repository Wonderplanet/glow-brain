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
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            // raw_price_stringを追加
            $table->string('raw_price_string', 32)->comment('クライアントから送られてきた単価付き購入価格')->after('raw_receipt');
        });
        // receipt_currency_codeをcurrency_codeにリネーム
        //  コメントを変更してからリネーム
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('receipt_currency_code', 16)->comment('ISO 4217の通貨コード')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('receipt_currency_code', 'currency_code');
        });
        // trigger系カラムの追加
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('trigger_type', 255)->comment('ショップ購入契機')->after('price_per_amount');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('trigger_id', 255)->comment('変動契機に対応するID')->after('trigger_type');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->text('trigger_detail')->comment('そのほかの付与情報')->after('trigger_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            // raw_price_stringを削除
            $table->dropColumn('raw_price_string');

            // trigger系カラムの削除
            $table->dropColumn('trigger_type');
            $table->dropColumn('trigger_id');
            $table->dropColumn('trigger_detail');
        });
        // currency_codeをreceipt_currency_codeにリネーム
        //  コメントを変更してからリネーム
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('currency_code', 16)->comment('レシート記載、ストアから送られてきた実際の通貨コード')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('currency_code', 'receipt_currency_code');
        });
    }
};
