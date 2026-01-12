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
        // account_idを削除してdevice_idを追加
        // usr
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子')->after('billing_platform');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子')->after('user_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子')->after('billing_platform');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropColumn('account_id');
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子')->after('billing_platform');
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
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('account_id', 255)->nullable()->comment('（取得できればプラットフォームのユーザー識別ID）')->after('billing_platform');
            $table->dropColumn('device_id');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropColumn('device_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('device_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('account_id', 255)->nullable()->comment('（取得できればプラットフォームのユーザー識別ID）')->after('billing_platform');
            $table->dropColumn('device_id');
        });
    }
};
