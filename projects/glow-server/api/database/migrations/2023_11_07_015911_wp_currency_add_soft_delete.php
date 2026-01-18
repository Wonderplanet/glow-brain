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
        // soft deleteカラムを追加
        // usr 
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            // MySQLだとtimestamp型になるが、timezoneを保持することを明示するためTzを使う
            $table->softDeletesTz();

            // user_idとdeleted_atで検索することがありそうなので、indexを設定する
            $table->index(['user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        // usr_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->softDeletesTz();

            // user_idとdeleted_atで検索することがありそうなので、indexを設定する
            $table->index(['user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->softDeletesTz();

            // user_idとdeleted_atで検索することがありそうなので、indexを設定する
            $table->index(['user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        // usr_store_infos
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->softDeletesTz();

            // user_idとdeleted_atで検索することがありそうなので、indexを設定する
            $table->index(['user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->softDeletesTz();

            // user_idとdeleted_atで検索することがありそうなので、indexを設定する
            $table->index(['user_id', 'deleted_at'], 'user_id_deleted_at_index');
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
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->dropIndex('user_id_deleted_at_index');
            $table->dropSoftDeletesTz();
        });
        // usr_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->dropIndex('user_id_deleted_at_index');
            $table->dropSoftDeletesTz();
        });
        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->dropIndex('user_id_deleted_at_index');
            $table->dropSoftDeletesTz();
        });
        // usr_store_infos
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->dropIndex('user_id_deleted_at_index');
            $table->dropSoftDeletesTz();
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropIndex('user_id_deleted_at_index');
            $table->dropSoftDeletesTz();
        });
    }
};
