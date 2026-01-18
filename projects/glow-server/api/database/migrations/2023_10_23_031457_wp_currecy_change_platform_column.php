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
        // platformをbilling_platformにリネーム
        // os_platformを追加

        // usr
        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('is_sandbox');
        });

        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('opr_product_id');
        });

        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('receipt_unique_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('receipt_bundle_id');
        });

        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('current_amount');
        });

        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('platform', 'billing_platform');
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('opr_product_id');
        });

        // log_currency_frees(os_platform)
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('user_id');
        });

        // log_currency_cashes(os_platform)
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('os_platform', 16)->comment('OSプラットフォーム')->after('user_id');
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
        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('billing_platform', 'platform');
            $table->dropColumn('os_platform');
        });
        // log_currency_frees(os_platform)
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->dropColumn('os_platform');
        });
        // log_currency_cashes(os_platform)
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->dropColumn('os_platform');
        });
    }
};
