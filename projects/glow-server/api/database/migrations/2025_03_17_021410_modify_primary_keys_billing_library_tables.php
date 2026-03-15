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
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_frees'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('UUID');
            $table->string('usr_user_id', 255)->primary();
            $table->bigInteger('ingame_amount')->default(0)->comment('ゲーム内・配布などで取得した無償一次通貨の所持数');
            $table->bigInteger('bonus_amount')->default(0)->comment("ショップ販売の追加付与で取得した無償一次通貨の所持数\n変動単価性の場合は発生しない");
            $table->bigInteger('reward_amount')->default(0)->comment('広告視聴などで発生する報酬から取得した無償一次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();
            $table->comment('ユーザーの所持する無償一次通貨の詳細');

            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_paids'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('UUID');
            $table->unsignedBigInteger('seq_no')->comment('登録した連番');
            $table->string('usr_user_id', 255)->index('user_id_index');
            $table->bigInteger('left_amount')->comment('同一単価・通貨での残所持数');
            $table->decimal('purchase_price', 20, 6)->comment("購入時のストアから送られてくる購入価格");
            $table->bigInteger('purchase_amount')->comment('購入時に取得した有償一次通貨数');
            $table->decimal('price_per_amount', 20, 8)->comment('単価');
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント');
            $table->string('currency_code', 16)->comment('ISO 4217の通貨コード');
            $table->string('receipt_unique_id', 255);
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0');
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->string('billing_platform', 16)->comment('AppStore / GooglePlay');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment('ユーザーの所持する有償一次通貨の詳細');
            $table->unique(['billing_platform', 'receipt_unique_id'], 'platform_receipt_unique_id_unique');
            $table->index(['usr_user_id', 'is_sandbox'], 'user_id_sandbox_index');
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
            $table->primary(['usr_user_id', 'seq_no']);
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_summaries'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('UUID');
            $table->string('usr_user_id', 255)->primary();
            $table->bigInteger('paid_amount_apple')->default(0)->comment('AppStoreで購入した有償一次通貨の所持数');
            $table->bigInteger('paid_amount_google')->default(0)->comment('GooglePlayで購入した有償一次通貨の所持数');
            $table->bigInteger('free_amount')->default(0)->comment('無償一次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment("ユーザーの所持する通貨の集約データ");
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_infos'));
        Schema::create(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('UUID');
            $table->string('usr_user_id', 255)->primary();
            $table->integer('age')->comment('年齢');
            $table->bigInteger('paid_price')->default(0)->comment('支払ったJPYの金額');
            $table->timestampTz('renotify_at')->nullable()->comment("次回年齢再確認する日時\nNULLなら再確認しなくて良い（20歳以上）");
            $table->bigInteger('total_vip_point')->default(0)->comment('商品購入時に獲得したVIPポイントの合計');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment('ユーザーのショップ登録情報');

            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_allowances'));
        Schema::create(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('id', 255)->unique()->comment('UUID');
            $table->string('usr_user_id', 255);
            $table->string('product_id', 255)->comment('ストアのプロダクトID');
            $table->string('mst_store_product_id', 255)->comment('mst_store_product_id');
            $table->string('product_sub_id', 255)->comment('購入対象のproduct_sub_id');
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->string('billing_platform', 16)->comment('AppStore / GooglePlay のどちらで購入フローをしようとしているか');
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーのショップ購入の許可確認状態');
            $table->primary(['usr_user_id', 'billing_platform', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_frees'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('usr_user_id', 255);
            $table->bigInteger('ingame_amount')->default(0)->comment('ゲーム内・配布などで取得した無償一次通貨の所持数');
            $table->bigInteger('bonus_amount')->default(0)->comment("ショップ販売の追加付与で取得した無償一次通貨の所持数\n変動単価性の場合は発生しない");
            $table->bigInteger('reward_amount')->default(0)->comment('広告視聴などで発生する報酬から取得した無償一次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();
            $table->comment('ユーザーの所持する無償一次通貨の詳細');

            $table->unique('usr_user_id', 'user_id_unique');
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_paids'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->bigInteger('seq_no')->unsigned()->comment('登録した連番');
            $table->string('usr_user_id', 255)->index('user_id_index');
            $table->bigInteger('left_amount')->comment('同一単価・通貨での残所持数');
            $table->decimal('purchase_price', 20, 6)->comment("購入時のストアから送られてくる購入価格");
            $table->bigInteger('purchase_amount')->comment('購入時に取得した有償一次通貨数');
            $table->decimal('price_per_amount', 20, 8)->comment('単価');
            $table->bigInteger('vip_point')->comment('商品購入時に獲得したVIPポイント');
            $table->string('currency_code', 16)->comment('ISO 4217の通貨コード');
            $table->string('receipt_unique_id', 255);
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0');
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->string('billing_platform', 16)->comment('AppStore / GooglePlay');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment('ユーザーの所持する有償一次通貨の詳細');
            $table->unique(['usr_user_id', 'seq_no'], 'user_id_seq_no_unique');
            $table->unique(['billing_platform', 'receipt_unique_id'], 'platform_receipt_unique_id_unique');
            $table->index(['usr_user_id', 'is_sandbox'], 'user_id_sandbox_index');
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_summaries'));
        Schema::create(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('usr_user_id', 255);
            $table->bigInteger('paid_amount_apple')->default(0)->comment('AppStoreで購入した有償一次通貨の所持数');
            $table->bigInteger('paid_amount_google')->default(0)->comment('GooglePlayで購入した有償一次通貨の所持数');
            $table->bigInteger('free_amount')->default(0)->comment('無償一次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment("ユーザーの所持する通貨の集約データ");
            $table->unique('usr_user_id', 'user_id_unique');
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_infos'));
        Schema::create(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('usr_user_id', 255);
            $table->integer('age')->comment('年齢');
            $table->bigInteger('paid_price')->default(0)->comment('支払ったJPYの金額');
            $table->timestampTz('renotify_at')->nullable()->comment("次回年齢再確認する日時\nNULLなら再確認しなくて良い（20歳以上）");
            $table->bigInteger('total_vip_point')->default(0)->comment('商品購入時に獲得したVIPポイントの合計');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');
            $table->softDeletesTz();

            $table->comment('ユーザーのショップ登録情報');

            $table->unique('usr_user_id', 'user_id_unique');
            $table->index(['usr_user_id', 'deleted_at'], 'user_id_deleted_at_index');
        });

        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_allowances'));
        Schema::create(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('usr_user_id', 255)->index('user_id_index');
            $table->string('product_id', 255)->comment('ストアのプロダクトID');
            $table->string('mst_store_product_id', 255)->comment('mst_store_product_id');
            $table->string('product_sub_id', 255)->comment('購入対象のproduct_sub_id');
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->string('billing_platform', 16)->comment('AppStore / GooglePlay のどちらで購入フローをしようとしているか');
            $table->string('device_id', 255)->nullable()->comment('ユーザーの使用しているデバイス識別子');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーのショップ購入の許可確認状態');
            $table->unique(['usr_user_id', 'billing_platform', 'product_id'], 'user_id_platform_product_id_unique');
        });
    }
};
