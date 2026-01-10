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
     * ※timestampsTzについて
     * MySQLではTzでもそうでなくてもtimestamp型になるが、timezoneを保持することを明示するためTzを使う
     * @return void
     */
    public function up()
    {
        // usr
        // usr_userテーブルはプロダクト側のテーブルを参照する想定のため作成しない

        Schema::create(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->bigInteger('paid_amount_apple')->default(0)->comment('AppStoreで購入した有償一次通貨の所持数');
            $table->bigInteger('paid_amount_google')->default(0)->comment('GooglePlayで購入した有償一次通貨の所持数');
            $table->bigInteger('free_amount')->default(0)->comment('無償一次通貨の所持数');
            $table->bigInteger('cash')->default(0)->comment('二次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment("ユーザーの所持する通貨の集約データ");
        });

        Schema::create(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->bigInteger('ingame_amount')->default(0)->comment('ゲーム内・配布などで取得した無償一次通貨の所持数');
            $table->bigInteger('bonus_amount')->default(0)->comment("ショップ販売の追加付与で取得した無償一次通貨の所持数\n変動単価性の場合は発生しない");
            $table->bigInteger('reward_amount')->default(0)->comment('広告視聴などで発生する報酬から取得した無償一次通貨の所持数');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーの所持する無償一次通貨の詳細');
        });

        Schema::create(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->bigInteger('left_amount')->comment('同一単価・通貨での残所持数');
            $table->decimal('purchase_price', 20, 6)->comment("購入時のストアから送られてくる購入価格");
            $table->bigInteger('purchase_amount')->comment('購入時に取得した有償一次通貨数');
            $table->decimal('price_per_amount', 20, 8)->comment('単価');
            $table->string('currency_code', 16)->comment('ISO 4217の通貨コード');
            $table->string('receipt_unique_id', 255)->unique('receipt_unique_id_unique');
            $table->tinyInteger('is_sandbox')->comment('サンドボックス・テスト課金から購入したら1, 本番購入なら0');
            $table->string('platform', 16)->comment('AppStore / GooglePlay');
            $table->timestampTz('purchased_at')->index('purchased_at_index')->comment('購入日時');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーの所持する有償一次通貨の詳細');
        });

        Schema::create(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->integer('age')->comment('年齢');
            $table->bigInteger('paid_price')->default(0)->comment('支払ったJPYの金額');
            $table->timestampTz('renotify_at')->nullable()->comment("次回年齢再確認する日時\nNULLなら再確認しなくて良い（20歳以上）");
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーのショップ登録情報');
        });

        Schema::create(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->string('opr_product_id', 255)->comment('購入対象のopr_productのID');
            $table->string('platform', 16)->comment('AppStore / GooglePlay のどちらで購入フローをしようとしているか');
            $table->string('account_id', 255)->nullable()->comment('（取得できればプラットフォームのユーザー識別ID）');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーのショップ購入の許可確認状態');
        });

        Schema::create(CurrencyDBUtility::getTableName('usr_store_product_histories'), function (Blueprint $table) {
            // TiDBではprimary keyを変更できないため、create_tableの時点でprimary keyの調整を行う
            //   idカラム自体は後の修正で追加されたものだが、上記の事情によりこのマイグレーションを直接修正する
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('receipt_unique_id', 255)->comment('プラットフォームごとの購入毎ユニークなIDを入れる');
            $table->string('user_id', 255)->index('user_id_index');
            $table->string('opr_product_id', 255)->comment('購入対象のopr_productのID');
            $table->string('platform', 16)->comment('AppStore / GooglePlay');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ユーザーのショップ購入履歴');
        });

        // log
        Schema::create(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('user_id', 255)->index('user_id_index');
            $table->string('platform_product_id', 255)->comment('プラットフォーム側で定義しているproduct_id');
            $table->string('mst_store_product_id', 255)->comment('マスターテーブルのプロダクトID');
            $table->string('opr_product_id', 255)->index('opr_product_id_index');
            $table->mediumText('raw_receipt')->comment('復号済み生レシートデータ');
            $table->string('receipt_currency_code', 16)->comment('レシート記載、ストアから送られてきた実際の通貨コード');
            $table->string('receipt_unique_id', 255)->comment('レシート記載、ユニークなID');
            $table->string('receipt_bundle_id', 255)->comment('レシート記載、ストアから送られてきた商品のバンドルID');
            $table->string('platform', 16)->comment('AppStore / GooglePlay');
            $table->bigInteger('paid_amount')->comment('有償一次通貨の付与量');
            $table->bigInteger('free_amount')->comment('無償一次通貨の付与量');
            $table->decimal('purchase_price', 20, 6)->comment('ストアから送られてきた実際の購入価格');
            $table->decimal('price_per_amount', 20, 8)->comment('単価');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('ショップ購入ログ');
        });
        Schema::create(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('user_id', 255)->index('user_id_index')->comment('ユーザーID');
            $table->string('currency_paid_id', 255)->index('currency_paid_id_index')->comment('変動した通貨テーブルのレコードID');
            $table->string('receipt_unique_id', 255)->nullable()->comment('このレコードを生成した購入レシートID（購入の場合）');

            $table->string('query', 16)->comment('どういう変化が起きたか');
            $table->decimal('purchase_price', 20, 6)->comment('購入時の価格');
            $table->bigInteger('purchase_amount')->comment('購入時に付与された個数');
            // 丸め誤差を避けるため、priceとamountは別途保存しておく
            $table->decimal('price_per_amount', 20, 8)->comment('単価');
            $table->string('currency_code', 16)->comment('ISO 4217の通貨コード');
            $table->bigInteger('before_amount')->comment('変更前の有償一次通貨の数');
            $table->bigInteger('change_amount')->comment("取得した有償一次通貨の数\n消費の場合は負");
            $table->bigInteger('current_amount')->comment("変動後現在でユーザーがプラットフォームに所持している有償一次通貨の数\n単価関係ない総数（summaryに入れる数）");
            $table->string('platform', 16)->comment('AppStore / GooglePlay');
            $table->string('trigger_type', 255)->comment('有償一次通貨の変動契機');
            $table->string('trigger_id', 255)->comment('変動契機に対応するID');
            $table->text('trigger_detail')->comment('そのほかの付与情報');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('有償一次通貨ログ');
        });

        Schema::create(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('user_id', 255)->index('user_id_index')->comment('ユーザーID');
            $table->bigInteger('before_ingame_amount')->comment('変更前のゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数');
            $table->bigInteger('before_bonus_amount')->comment('変更前のショップ販売の追加ボーナスで取得した無償一次通貨の数');
            $table->bigInteger('before_reward_amount')->comment('変更前の広告視聴等の報酬で取得した無償一次通貨の数');
            $table->bigInteger('change_ingame_amount')->comment("ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の数\n消費の場合は負");
            $table->bigInteger('change_bonus_amount')->comment("ショップ販売の追加ボーナスで取得した無償一次通貨の数\n消費の場合は負");
            $table->bigInteger('change_reward_amount')->comment("広告視聴等の報酬で取得した無償一次通貨の数\n消費の場合は負");
            $table->bigInteger('current_ingame_amount')->comment('ゲーム内配布（クエストクリアなど）や補填で取得した無償一次通貨の現在の数');
            $table->bigInteger('current_bonus_amount')->comment('ショップ販売の追加ボーナスで取得した無償一次通貨の現在の数');
            $table->bigInteger('current_reward_amount')->comment('広告視聴等の報酬で取得した無償一次通貨の現在の数');
            $table->string('trigger_type', 255)->comment('無償一次通貨の変動契機');
            $table->string('trigger_id', 255)->comment('変動契機に対応するID');
            $table->text('trigger_detail')->comment('そのほかの付与情報');
            $table->timestampTz('created_at')->index('created_at_index');
            $table->timestampTz('updated_at');

            $table->comment('無償一次通貨ログ');
        });
        Schema::create(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('user_id', 255)->index('user_id_index')->comment('ユーザーID');
            $table->bigInteger('before_amount')->comment('変更前の二次通貨の数');
            $table->bigInteger('change_amount')->comment("取得した二次通貨の数\n消費の場合は負");
            $table->bigInteger('current_amount')->comment('二次通貨の現在の数');
            $table->string('trigger_type', 255)->comment('二次通貨の変動契機');
            $table->string('trigger_id', 255)->comment('変動契機に対応するID');
            $table->text('trigger_detail')->comment('そのほかの付与情報');
            $table->timestampTz('created_at')->index('created_at_index');
            $table->timestampTz('updated_at');

            $table->comment('二次通貨ログ');
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
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_summaries'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_frees'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_currency_paids'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_infos'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_allowances'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('usr_store_product_histories'));

        // log
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_stores'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_paids'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_frees'));
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_cashes'));
    }
};
