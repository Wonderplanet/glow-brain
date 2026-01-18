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
        // log
        // trigger_nameカラムを追加
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('trigger_name', 255)->comment('変動契機の日本語名')->after('trigger_id');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->string('trigger_name', 255)->comment('変動契機の日本語名')->after('trigger_id');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('trigger_name', 255)->comment('変動契機の日本語名')->after('trigger_id');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('trigger_name', 255)->comment('変動契機の日本語名')->after('trigger_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('trigger_name', 255)->comment('変動契機の日本語名')->after('trigger_id');
        });

        // log_stores
        // ageを保存するカラムを追加
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->integer('age')->comment('年齢')->after('device_id');
        });

        // log_stores
        // product_sub_idに対応する名前をログとして入れておくカラムを追加
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('product_sub_name', 255)->comment('実際の販売商品名')->after('product_sub_id');
        });

        // 検索用indexを追加
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');

            //   currency_codeとuser_idとpurchase_priceとis_sandbox (有償分の合計金額の集計に使用)
            $table->index(
                ['is_sandbox', 'user_id', 'currency_code', 'purchase_price'],
                'user_id_currency_code_purchase_price_index'
            );
        });

        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            //   receipt_unique_id
            $table->index('receipt_unique_id', 'receipt_unique_id_index');

            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');
        });

        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');
        });

        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');
        });

        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // log
        // 検索用indexを削除
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            //   trigger_id
            $table->dropIndex('trigger_id_index');

            //   trigger_name
            $table->dropIndex('trigger_name_index');

            //   currency_codeとuser_idとpurchase_priceとis_sandbox (有償分の合計金額の集計に使用)
            $table->dropIndex('user_id_currency_code_purchase_price_index');
        });

        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            //   receipt_unique_id
            $table->dropIndex('receipt_unique_id_index');

            //   trigger_id
            $table->dropIndex('trigger_id_index');

            //   trigger_name
            $table->dropIndex('trigger_name_index');
        });

        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            //   trigger_id
            $table->dropIndex('trigger_id_index');

            //   trigger_name
            $table->dropIndex('trigger_name_index');
        });

        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            //   trigger_id
            $table->dropIndex('trigger_id_index');

            //   trigger_name
            $table->dropIndex('trigger_name_index');
        });

        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            //   trigger_id
            $table->dropIndex('trigger_id_index');

            //   trigger_name
            $table->dropIndex('trigger_name_index');
        });

        // log_stores
        // product_sub_idに対応する名前をログとして入れておくカラムを削除
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('product_sub_name');
        });


        // log_stores
        // ageを保存するカラムを削除
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('age');
        });

        // trigger_nameカラムを削除
        // log_stores
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('trigger_name');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('trigger_name');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->dropColumn('trigger_name');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->dropColumn('trigger_name');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropColumn('trigger_name');
        });
    }
};
