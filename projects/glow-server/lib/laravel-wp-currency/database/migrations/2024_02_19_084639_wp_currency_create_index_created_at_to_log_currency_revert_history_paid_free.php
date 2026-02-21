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
        // log_currency_revert_history_paid_logs.created_atにindexを追加
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'), function (Blueprint $table) {
            $table->index('created_at', 'created_at_index');
        });
        // log_currency_revert_history_free_logs.created_atにindexを追加
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'), function (Blueprint $table) {
            $table->index('created_at', 'created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'), function (Blueprint $table) {
            $table->dropIndex('created_at_index');
        });
        Schema::table(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'), function (Blueprint $table) {
            $table->dropIndex('created_at_index');
        });
    }
};
