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
        // logテーブルにrequest_idカラムを追加
        //   どのリクエストで処理されたかを識別するためのID
        //   nginxのrequest_idを使用する想定。取得できない場合はuuidを入れる。
        //   有償一次通貨返却時に、同時に処理されたことを識別するために使用する

        // log
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->string('request_id', 255)->comment('リクエスト識別ID')->after('trigger_detail');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->string('request_id', 255)->comment('リクエスト識別ID')->after('trigger_detail');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->string('request_id', 255)->comment('リクエスト識別ID')->after('trigger_detail');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('request_id', 255)->comment('リクエスト識別ID')->after('trigger_detail');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('request_id', 255)->comment('リクエスト識別ID')->after('trigger_detail');
        });

        // 返却を記録するためのテーブルを追加
        // log_currency_reverts
        Schema::create(CurrencyDBUtility::getTableName('log_currency_revert_histories'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->comment('ユーザーID');
            $table->text('comment')->comment('コメント');

            // 変更した通貨情報
            //   これらの情報でひとつの返却単位としている
            $table->string('log_trigger_type', 255)->comment('対象ログのトリガータイプ');
            $table->string('log_trigger_id', 255)->comment('対象ログのトリガーID');
            $table->string('log_trigger_name', 255)->comment('対象ログのトリガー名');
            $table->string('log_request_id', 255)->comment('対象ログのリクエストID');
            $table->timestampTz('log_created_at')->comment('対象ログの作成日時');
            //   変更する対象の内容を記録するため、マイナス値のものを入れる
            $table->bigInteger('log_change_paid_amount')->comment('変更対象の有償通貨');
            $table->bigInteger('log_change_free_amount')->comment('変更対象の無償通貨');

            $table->string('trigger_type', 255)->comment('トリガータイプ');
            $table->string('trigger_id', 255)->comment('トリガーID');
            $table->string('trigger_name', 255)->comment('トリガー名');
            $table->string('trigger_detail', 255)->comment('トリガー詳細');
            $table->string('request_id', 255)->comment('リクエスト識別ID');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->index('user_id', 'user_id_index');
            $table->index('created_at', 'created_at_index');

            $table->comment('一次通貨返却ログ');
        });

        // 返却した対象のログとlog_currency_revertsの紐付けを行うためのテーブルを追加
        // filamentで扱いやすくするためにIDのリレーションテーブルを作る
        //   log_currency_revertsとlog_currency_paidsの紐付け
        Schema::create(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->comment('ユーザーID');
            $table->string('log_currency_revert_history_id', 255)->comment('log_currency_revert_historiesのID');
            $table->string('log_currency_paid_id', 255)->comment('実行した際のlog_currency_paidsのID');
            $table->string('revert_log_currency_paid_id', 255)->comment('log_currency_paidsの返却対象としたログのID');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->index(['user_id'], 'user_id_index');

            $table->comment('一次通貨返却ログと有償一次通貨ログの紐付け');
        });
        //  log_currency_revertsとlog_currency_freesの紐付け
        Schema::create(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->comment('ユーザーID');
            $table->string('log_currency_revert_history_id', 255)->comment('log_currency_revert_historiessのID');
            $table->string('log_currency_free_id', 255)->comment('実行した際のlog_currency_freesのID');
            $table->string('revert_log_currency_free_id', 255)->comment('log_currency_freesの返却対象としたログのID');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->index(['user_id'], 'user_id_index');

            $table->comment('一次通貨返却ログと無償一次通貨ログの紐付け');
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
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
        // log_currency_paids
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
        // log_currency_frees
        Schema::table(CurrencyDBUtility::getTableName('log_currency_frees'), function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
        // log_currency_cashes
        Schema::table(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->dropColumn('request_id');
        });
        // log_allowances
        Schema::table(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->dropColumn('request_id');
        });

        // 返却を記録するためのテーブルを追加
        // log_currency_reverts
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_revert_histories'));
        // 返却した対象のログとlog_currency_revertsの紐付けを行うためのテーブルを追加
        //   log_currency_revertsとlog_currency_paidsの紐付け
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_revert_history_paid_logs'));
        //  log_currency_revertsとlog_currency_freesの紐付け
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_revert_history_free_logs'));
    }
};
