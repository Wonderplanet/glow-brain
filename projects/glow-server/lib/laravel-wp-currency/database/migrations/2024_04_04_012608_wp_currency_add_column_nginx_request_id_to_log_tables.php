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
        // 各ログテーブルにnginx_request_idを追加
        //  今後の調査の為でリクエストを特定できるようにする為にnginxのリクエストIDを保持するカラムを追加
        //  既存の「request_id」と異なり、nginxのリクエストIDを取得できなかった場合は空文字を入れる想定
        //  log_currency_revert_history_free_logs、log_currency_revert_history_paid_logsはrequest_idと同様に除外している
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID')->after('request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('nginx_request_id');
        });
    }
};
