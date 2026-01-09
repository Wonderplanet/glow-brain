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
        // opr_product_idカラムをproduct_sub_idにリネームする
        // usr
        // usr_store_allowances
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('opr_product_id', 255)->comment('購入対象のproduct_sub_id')->change();
        });
        // renameColumnでコメントが戻されてしまうので、別々のブロックにする必要がある
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('opr_product_id', 'product_sub_id');
        });

        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('opr_product_id', 255)->comment('購入対象のproduct_sub_id')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('opr_product_id', 'product_sub_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('opr_product_id', 255)->comment('購入対象のproduct_sub_id')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('opr_product_id', 'product_sub_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('opr_product_id', 255)->comment('購入対象のproduct_sub_id')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('opr_product_id', 'product_sub_id');
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
            $table->string('product_sub_id', 255)->comment('購入対象のopr_productのID')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->renameColumn('product_sub_id', 'opr_product_id');
        });
        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('product_sub_id', 255)->comment('購入対象のopr_productのID')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->renameColumn('product_sub_id', 'opr_product_id');
        });

        // log
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('product_sub_id', 255)->comment('購入対象のopr_productのID')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->renameColumn('product_sub_id', 'opr_product_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('product_sub_id', 255)->comment('購入対象のopr_productのID')->change();
        });
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->renameColumn('product_sub_id', 'opr_product_id');
        });
    }
};
