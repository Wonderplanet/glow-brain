<?php

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
        // is_sandboxを追加、indexを追加
        // usr
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0')->after('price_per_amount');
        });
		Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
			$table->index(['user_id', 'is_sandbox'], 'user_id_sandbox_index');
		});
		// usr_currency_paids(indexのみ)
		Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
			$table->index(['user_id', 'is_sandbox'], 'user_id_sandbox_index');
		});

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0')->after('price_per_amount');
        });
		Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
			$table->index(['user_id', 'is_sandbox'], 'user_id_sandbox_index');
		});
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0')->after('receipt_unique_id');
        });
		Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
			$table->index(['user_id', 'is_sandbox'], 'user_id_sandbox_index');
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
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
			$table->dropIndex('user_id_sandbox_index');
            $table->dropColumn('is_sandbox');
        });
		// usr_currency_paids(indexのみ)
		Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
			$table->dropIndex('user_id_sandbox_index');
		});

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
			$table->dropIndex('user_id_sandbox_index');
            $table->dropColumn('is_sandbox');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
			$table->dropIndex('user_id_sandbox_index');
            $table->dropColumn('is_sandbox');
        });
    }
};
