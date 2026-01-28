<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * WebStore（Xsolla）連携用テーブルを作成
     */
    public function up(): void
    {
        // 1. usr_webstore_infos: WebStoreユーザー情報テーブル
        Schema::create('usr_webstore_infos', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->primary()->comment('ユーザーID');
            $table->string('country_code', 10)->comment('ISO 3166-1 alpha-2形式の国コード（例: JP, US, GB）');
            $table->string('os_platform', 20)->nullable()->comment('OSプラットフォーム（ios/android）※Adjust S2S形式');
            $table->string('ad_id', 255)->nullable()->comment('広告ID（IDFA/GAID）※Adjust S2S送信用');
            $table->timestampsTz();
        });

        // 2. usr_webstore_transactions: WebStoreトランザクション状態管理テーブル
        Schema::create('usr_webstore_transactions', function (Blueprint $table) {
            $table->string('id', 255);
            $table->string('usr_user_id', 255)->comment('usr_users.id');
            $table->string('transaction_id', 255)->comment('トランザクションID（UUID v4）');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Xsollaの注文ID');
            $table->tinyInteger('is_sandbox')->default(0)->comment('テスト決済フラグ（0: 本番, 1: sandbox）');
            $table->string('status', 50)->comment('ステータス(pending, completed, failed)');
            $table->string('error_code', 100)->nullable()->comment('エラーコード');
            $table->string('item_grant_status', 50)->nullable()->comment('アイテム付与ステータス');
            $table->string('bank_status', 50)->nullable()->comment('Bank連携ステータス');
            $table->string('adjust_status', 50)->nullable()->comment('Adjust連携ステータス');
            $table->timestampsTz();

            // インデックス
            $table->unique('transaction_id', 'uk_transaction_id');
            $table->index('usr_user_id', 'idx_usr_user_id');
            $table->index('order_id', 'idx_order_id');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usr_webstore_transactions');
        Schema::dropIfExists('usr_webstore_infos');
    }
};
