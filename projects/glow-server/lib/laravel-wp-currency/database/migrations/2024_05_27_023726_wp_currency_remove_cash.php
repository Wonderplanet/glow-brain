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
        // currency_summaryからcashを削除
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->dropColumn('cash');
        });

        // log_currency_cashを削除
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_currency_cashes'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // cashを追加
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->bigInteger('cash')->default(0)->comment('二次通貨の所持数')->after('free_amount');
        });

        // log_currency_cashを追加
        Schema::create(CurrencyDBUtility::getTableName('log_currency_cashes'), function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('os_platform', 16)->comment('OSプラットフォーム');
            $table->string('usr_user_id', 255)->index('user_id_index')->comment('ユーザーID');
            $table->bigInteger('before_amount')->comment('変更前の二次通貨の数');
            $table->bigInteger('change_amount')->comment("取得した二次通貨の数\n消費の場合は負");
            $table->bigInteger('current_amount')->comment('二次通貨の現在の数');
            $table->string('trigger_type', 255)->comment('二次通貨の変動契機');
            $table->string('trigger_id', 255)->comment('変動契機に対応するID');
            $table->string('trigger_name', 255)->comment('変動契機の日本語名');
            $table->text('trigger_detail')->comment('そのほかの付与情報');
            $table->string('request_id', 255)->comment('リクエスト識別ID');
            $table->string('nginx_request_id', 255)->comment('nginxのリクエスト識別ID');

            $table->timestampTz('created_at')->index('created_at_index');
            $table->timestampTz('updated_at');

            //   trigger_id
            $table->index('trigger_id', 'trigger_id_index');

            //   trigger_name
            $table->index('trigger_name', 'trigger_name_index');

            $table->comment('二次通貨ログ');
        });
    }
};
