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
     * @return void
     */
    public function up()
    {
        // usr
        // TiDBの仕様では、ALTER TABLE実行前のカラムのみ参照できるため、追加するカラムにafterを使用する場合はクエリを分割する
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('product_id', 255)->comment('ストアの製品ID')->after('user_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->string('mst_store_product_id', 255)->comment('mst_store_product_id')->after('product_id');
        });
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            // user_id, platform, product_idでunique
            $table->unique(['user_id', 'platform', 'product_id'], 'user_id_platform_product_id_unique');
        });

        // log
        Schema::create(CurrencyDBUtility::getTableName('log_allowances'), function (Blueprint $table) {
            $table->string('id', 255)->primary()->comment('UUID');
            $table->string('user_id', 255)->index('user_id_index');
            $table->string('opr_product_id', 255)->comment('購入対象のopr_productのID');
            $table->string('product_id', 255)->comment('ストアの製品ID');
            $table->string('mst_store_product_id', 255)->comment('mst_store_product_id');
            $table->string('platform', 16)->comment('AppStore / GooglePlay のどちらで購入フローをしようとしているか');
            $table->string('account_id', 255)->nullable()->comment('（取得できればプラットフォームのユーザー識別ID）');
            $table->string('trigger_type', 255)->comment('alllowanceの変更契機');
            $table->string('trigger_id', 255)->comment('対象のallowanceのID');
            $table->text('trigger_detail')->comment('そのほかの付与情報');
            $table->timestampTz('created_at');
            $table->timestampTz('updated_at');

            $table->comment('購入許可ログ');
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
        Schema::table(CurrencyDBUtility::getTableName('usr_store_allowances'), function (Blueprint $table) {
            $table->dropColumn('product_id');
            $table->dropColumn('mst_store_product_id');
            $table->dropUnique('user_id_platform_product_id_unique');
        });

        // log
        Schema::dropIfExists(CurrencyDBUtility::getTableName('log_allowances'));
    }
};
