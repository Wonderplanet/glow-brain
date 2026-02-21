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
     * WebStore（外部決済）サポートのため、通貨関連テーブルにカラムを追加
     * 1. usr_store_product_histories: WebStore購入履歴の記録
     * 2. usr_currency_summaries: プラットフォーム間共有可能な有償通貨残高
     */
    public function up(): void
    {
        // ========================================
        // 1. usr_store_product_histories
        // ========================================
        // TiDBの仕様では、ALTER TABLE実行前のカラムのみ参照できるため、追加するカラムにafterを使用する場合はクエリを分割する

        // order_idカラムを追加（Xsolla仕様ではintegerだがbigintで保存）
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->comment('Xsollaの注文ID（外部決済の場合のみ設定）')->after('receipt_unique_id');
        });

        // invoice_idカラムを追加
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('invoice_id', 255)->nullable()->comment('Xsollaの請求書ID（無料アイテム・クーポン交換の場合はNULL）')->after('order_id');
        });

        // transaction_idカラムを追加
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('transaction_id', 255)->nullable()->comment('W2で発行したトランザクションID（外部決済の場合のみ設定）')->after('invoice_id');
        });

        // order_idのユニークインデックスを追加（べき等性保証）
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->unique('order_id', 'idx_order_id');
        });

        // currency_codeをNULL許容に変更（無料アイテム対応）
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->string('currency_code', 16)->nullable()->change();
        });

        // ========================================
        // 2. usr_currency_summaries
        // ========================================
        // プラットフォーム間で共有可能な有償通貨残高カラムを追加
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->bigInteger('paid_amount_share')->default(0)->comment('プラットフォーム間で共有可能な有償通貨残高（iOS/Androidどちらからでも消費可能）')->after('paid_amount_google');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // usr_currency_summaries
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->dropColumn('paid_amount_share');
        });

        // usr_store_product_histories
        Schema::table(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            $table->dropUnique('idx_order_id');
            $table->dropColumn(['order_id', 'invoice_id', 'transaction_id']);
        });

        // 注意: currency_codeのNOT NULL制約は戻さない
        // (既存データがNULLになっている可能性があるため)
    }
};
