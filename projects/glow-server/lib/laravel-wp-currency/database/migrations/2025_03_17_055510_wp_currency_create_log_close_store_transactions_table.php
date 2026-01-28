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
        /**
         * MEMO
         *  2025_03_17_055510_create_log_close_store_transactions_table として作成されていたが、laravel-wp-currencyのマイグレーションとして管理する為下記のようにした
         *   - 2025_03_17_055510_create_log_close_store_transactions_tableのup/downメソッドの中身を空にした
         *   - このマイグレーションファイル(2025_03_17_055510_wp_currency_create_log_close_store_transactions_table)を用意して、元のマイグレーションの中身を移動
         *   - log_close_store_transactionsテーブルが存在しない場合はこのマイグレーションで作成する
         */
        if (!Schema::hasTable(CurrencyDBUtility::getTableName('log_close_store_transactions'))) {
            // テーブルが存在しない場合は作成する
            Schema::create(CurrencyDBUtility::getTableName('log_close_store_transactions'), function (Blueprint $table) {
                $table->string('id', 255)->primary();
                $table->string('usr_user_id', 255)->index('user_id_index')->comment('ユーザーID');
                $table->string('platform_product_id', 255)->index('platform_product_id_index')->comment('プラットフォーム側で定義しているproduct_id');
                $table->string('mst_store_product_id', 255)->index('mst_store_product_id_index')->comment('マスターテーブルのプロダクトID');
                $table->string('product_sub_id', 255)->index('product_sub_id_index')->comment('購入対象のproduct_sub_id');
                $table->string('product_sub_name', 255)->comment('実際の販売商品名');
                $table->mediumText('raw_receipt')->comment('復号済み生レシートデータ');
                $table->string('raw_price_string', 32)->comment('クライアントから送られてきた単価付き購入価格');
                $table->string('currency_code', 16)->comment('ISO 4217の通貨コード');
                $table->string('receipt_unique_id', 255)->index('receipt_unique_id_index')->comment('レシート記載、ユニークなID');
                $table->string('receipt_bundle_id', 255)->index('receipt_bundle_id_index')->comment('レシート記載、ストアから送られてきた商品のバンドルID');
                $table->string('os_platform', 16)->comment('OSプラットフォーム');
                $table->string('billing_platform', 16)->comment('AppStore / GooglePlay');
                $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子');
                $table->string('purchase_price', 20, 6)->comment('ストアから送られてきた実際の購入価格');
                $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0');
                $table->string('log_store_id', 255)->index('log_stores_user_id_index')->comment('失敗したストア購入ログのレコードID');
                $table->string('usr_store_product_history_id', 255)->index('usr_store_product_histories_user_id_index')->comment('失敗したストア商品購入テーブルのレコードID');
                $table->string('trigger_type', 255)->comment('ロギング契機');
                $table->string('trigger_name', 255)->comment('ロギング契機の日本語名');
                $table->string('trigger_id', 255)->index('trigger_id_index')->comment('ロギング契機に対応するID');
                $table->text('trigger_detail')->nullable()->comment('その他の付与情報 (JSON)');
                $table->string('request_id_type', 255)->comment('リクエスト識別IDの種類');
                $table->string('request_id', 255)->index('request_id_index')->comment('リクエスト識別ID');
                $table->string('nginx_request_id', 255)->index('nginx_request_id_index')->comment('nginxのリクエスト識別ID');
                $table->timestampTz('created_at')->comment('作成日時');
                $table->timestampTz('updated_at')->comment('更新日時');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_close_store_transactions');
    }
};
