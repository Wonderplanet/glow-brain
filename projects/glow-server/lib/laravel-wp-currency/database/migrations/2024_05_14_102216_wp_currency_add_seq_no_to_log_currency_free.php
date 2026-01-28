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
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            // logging_noを追加
            $table->unsignedBigInteger('logging_no')->after('id')->comment('ログ登録番号');

            // logging_noとcreated_atの複合インデックスを追加
            $table->index(['created_at', 'logging_no'], 'created_at_logging_no_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            // 複合インデックスを削除
            $table->dropIndex('created_at_logging_no_index');

            // logging_noを削除
            $table->dropColumn('logging_no');
        });
    }
};
