<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use WonderPlanet\Domain\Currency\Utils\DBUtility as CurrencyDBUtility;

/**
 * 課金・通貨基盤向けマイグレーション
 * laravel-wp-billing,laravel-wp-currencyに対応
 *
 * テーブルやコネクション名は外部から指定できるようにする想定のため、テーブル作成はフレームワーク全体のマイグレーションに含める
 *
 * 変更履歴などはlib/laravel-wp-currnecy 以下にある同名のファイルを参照すること
 *
 * ※注意※
 * VQ側でデフォルトコネクションを変更してマイグレーションを行っている箇所があるため、現時点ではそれに合わせる。
 * マイグレーションファイル内ではSchema::createなどを使ってデフォルトのコネクションを参照すること
 */

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // mst
        // プラットフォーム（AppStore, GooglePlay）に登録している商品のIDを管理する
        // リストアがあるため一度設定したデータは変えないこと
        Schema::create(CurrencyDBUtility::getTableName('mst_store_products'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->bigInteger('release_key')->comment('変更が適用されるリリースキー');
            $table->string('product_id_ios', 100)->index('product_id_ios_index')->comment('AppStoreの商品ID');
            $table->string('product_id_android', 40)->index('product_id_android_index')->comment('GooglePlayの商品ID');

            $table->comment("プラットフォーム（AppStore, GooglePlay）に登録している商品のIDを管理する\nリストアがあるため一度定義したら変えない");
        });

        // opr
        // プロダクトごとに変更される可能性のあるカラムはデフォルトで用意しない
        Schema::create(CurrencyDBUtility::getTableName('opr_products'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('mst_store_product_id', 255)->index('mst_store_product_id_index');
            $table->bigInteger('paid_amount')->default(0)->comment('配布する有償一次通貨');

            $table->comment("ユーザーに販売する実際の商品を管理する\n1mst_store_productに対して複数");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(CurrencyDBUtility::getTableName('mst_store_products'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('opr_products'));
    }
};
