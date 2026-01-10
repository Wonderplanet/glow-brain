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
        // log
        // request_id_typeを追加
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類')->default('')->after('trigger_detail');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類')->default('')->after('trigger_detail');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類')->default('')->after('trigger_detail');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類')->default('')->after('trigger_detail');
        });
        // log_currency_revert_histories
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類')->default('')->after('log_created_at');
        });

        // log_currency_revert_historiesにlog_request_id_typeを追加
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->string('log_request_id_type', 255)->comment('対象ログのリクエスト識別IDの種類')->default('')->after('log_trigger_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // log
        // request_id_typeのカラムを削除
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('request_id_type');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('request_id_type');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->dropColumn('request_id_type');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropColumn('request_id_type');
        });
        // log_currency_revert_histories
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->dropColumn('request_id_type');
        });

        // log_currency_revert_historiesからlog_request_id_typeを削除
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->dropColumn('log_request_id_type');
        });
    }
};
