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
        // usr_users.user_idを参照するカラム名をusr_user_idに変更する

        // usr
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // usr_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // usr_store_infos
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_revert_histories
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_revert_history_paid_logs
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });
        // log_currency_revert_history_free_logs
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'), function (Blueprint $table) {
            $table->renameColumn('user_id', 'usr_user_id');
        });

        // created_atにindexが抜けているから追加
        //  log_currency_paids 
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->index('created_at', 'created_at_index');
        });

        //  log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->index('created_at', 'created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // usr
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // usr_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // usr_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // usr_store_infos
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_revert_histories
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_revert_history_paid_logs
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });
        // log_currency_revert_history_free_logs
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'), function (Blueprint $table) {
            $table->renameColumn('usr_user_id', 'user_id');
        });

        // created_atにindexが抜けているから追加
        //  log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropIndex('created_at_index');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropIndex('created_at_index');
        });
    }
};
