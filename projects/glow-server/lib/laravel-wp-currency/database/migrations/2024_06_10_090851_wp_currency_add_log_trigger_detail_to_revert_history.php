<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

/**
 * log_currency_revert_historiesのスキーマ修正
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // log 
        // log_currency_revert_historiesのスキーマ修正
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            // log_trigger_detailを追加
            $table->text('log_trigger_detail')->comment('対象ログのそのほかの付与情報')->after('log_trigger_name');
            // trigger_detailをtextに変更
            $table->text('trigger_detail')->comment('トリガー詳細')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // log 
        // log_currency_revert_historiesのスキーマ修正
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            // log_trigger_detailを削除
            $table->dropColumn('log_trigger_detail');
            // trigger_detailをstringに戻す
            $table->string('trigger_detail', 255)->comment('トリガー詳細')->change();
        });
    }
};
