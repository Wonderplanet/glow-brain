<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $eventIds = [
            100, 200, 300
        ];
        $platformIds = [
            'ios', 'android', 'pc', 'dmm', 'steam', 'ps4', 'ps5', 'xsx', 'nsw', 'win',
        ];

        Schema::create('adm_bank_f001', function (Blueprint $table) use (
            $platformIds,
            $eventIds,
        ) {
            $table->string('id')->primary();
            $table->string('fluentd_tag', 50)->comment('fluent_loggerの送信先タグ名');
            $table->string('version', 10)->default('1.0')->comment('データフォーマットのバージョン');
            $table->enum('event_id', $eventIds)->comment('イベントID');
            $table->timestampTz('event_time')->comment('イベント発生日時');
            $table->string('app_id', 10)->comment('アプリケーションID');
            $table->string('app_user_id', 128)->comment('ユーザーID');
            $table->string('app_system_prefix', 10)->comment('システム識別子');
            $table->string('user_id', 128)->comment('BNIDのUserID');
            $table->string('person_id', 128)->comment('BNIDのPersonID');
            $table->string('mbid', 128)->comment('BNIDのMBID');
            $table->string('ktid', 46)->comment('BNIDのかんたんID');
            $table->enum('platform_id', $platformIds)->comment('プラットフォームID');
            $table->string('platform_version', 50)->comment('プラットフォームバージョン');
            $table->string('platform_user_id', 50)->comment('プラットフォーム別識別番号');
            $table->string('user_agent', 350)->comment('ユーザーエージェント');
            $table->timestampTz('created_time')->comment('ユーザー初回登録日時');
            $table->string('country_code', 2)->default('JP')->comment('国コード');
            $table->string('ad_id', 255)->nullable()->comment('広告ID');
            $table->timestampsTz();
        });

        Schema::create('adm_bank_f002', function (Blueprint $table) use (
            $platformIds,
        ) {
            $table->string('id')->primary();
            $table->string('fluentd_tag', 50)->comment('fluent_loggerの送信先タグ名');
            $table->string('version', 10)->default('1.0')->comment('データフォーマットのバージョン');
            $table->string('app_id', 10)->comment('アプリケーションID');
            $table->string('app_user_id', 128)->comment('ユーザーID');
            $table->string('app_system_prefix', 10)->comment('システム識別子');
            $table->enum('platform_id', $platformIds)->comment('プラットフォームID');
            $table->integer('buy_coin')->comment('購入コイン数');
            $table->double('buy_amount')->comment('購入コイン数');
            $table->integer('pay_coin')->comment('消費コイン数');
            $table->double('pay_amount')->comment('消費コイン数');
            $table->double('direct_amount')->comment('直接課金金額');
            $table->double('subscription_amount')->comment('定期購入金額');
            $table->string('item_id', 50)->comment('購入アイテムID');
            $table->timestampTz('insert_time')->comment('コイン購入/消費日時');
            $table->string('country_code', 2)->default('JP')->comment('国コード');
            $table->string('currency_code', 3)->default('JPY')->comment('通貨コード');
            $table->string('ad_id', 255)->nullable()->comment('広告ID');
            $table->timestampsTz();
        });

        Schema::create('adm_bank_f003', function (Blueprint $table) use (
            $platformIds,
        ) {
            $table->string('id')->primary();
            $table->string('app_id', 10)->comment('アプリケーションID');
            $table->enum('platform_id', $platformIds)->comment('プラットフォームID');
            $table->unsignedInteger('date')->comment('年月日(YYYYMM, YYYYMMDD)');
            $table->double('total_sales')->comment('期間内売上(有償通貨)');
            $table->text('data')->comment('有償通貨情報');
            $table->double('direct_total_sales')->comment('期間内売上(直接課金)');
            $table->text('direct_data')->comment('直接課金情報');
            $table->double('subscription_total_sales')->comment('期間内売上(定期購入)');
            $table->text('subscription_data')->comment('定期購入情報');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adm_bank_f003');
        Schema::dropIfExists('adm_bank_f002');
        Schema::dropIfExists('adm_bank_f001');
    }
};
