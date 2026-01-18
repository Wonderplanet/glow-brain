<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        ############################
        // 有償一次通貨の登録順を確定するためにシリアルナンバーのカラムを追加する
        //   user_id, seq_noで一意になる
        //   seq_noは手動で割り振る
        //     TiDBのauto_incrementは後から追加したレコードが大きい番号となることが保証されないため
        // usr_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            // seq_noの追加
            $table->bigInteger('seq_no')->unsigned()->comment('登録した連番')->after('id');
        });

        // 既存のレコードがある場合はseq_noを振り直す
        //   そうしないとユニーク制約が設定できない
        // user_idごと、created_atの昇順にseq_noをつける
        $usrCurrencyPaidTableName = CurrencyDBUtility::getTableName('usr_currency_paids');
        $sql = <<< SQL
        UPDATE {$usrCurrencyPaidTableName} ucp,
        (
            -- サブクエリでuser_idごと、created_atの昇順にseq_noをつける
            SELECT id, user_id,
            ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY created_at) AS seq_no
            FROM {$usrCurrencyPaidTableName}
        ) seq_no_table
        SET
            ucp.seq_no = seq_no_table.seq_no
        WHERE
            ucp.id = seq_no_table.id
        ;
SQL;

        DB::statement($sql);

        // データ投入後にユニーク制約を追加する
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            // user_id, seq_no
            $table->unique(['user_id', 'seq_no'], 'user_id_seq_no_unique');

            // seq_noを消費順のために追加したのでpurchased_atを削除
            $table->dropIndex('purchased_at_index');
            $table->dropColumn('purchased_at');
        });

        // log
        //   購入ログとusr_currency_paidを紐づけるためseq_noを追加する
        // log_store
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            // seq_no
            $table->bigInteger('seq_no')->unsigned()->comment('登録した連番')->after('id');
        });
        // log_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            // seq_no
            $table->bigInteger('seq_no')->unsigned()->comment('登録した連番')->after('id');
        });

        ############################
        // user_idで一意となるはずのカラムにunique制約を追加する
        // usr_currency_summary
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            // user_id
            $table->unique('user_id', 'user_id_unique');

            // unique制約があればindexは不要なので削除
            $table->dropIndex('user_id_index');
        });

        // usr_currency_free
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            // user_id
            $table->unique('user_id', 'user_id_unique');

            // unique制約があればindexは不要なので削除
            $table->dropIndex('user_id_index');
        });

        // usr_store_info
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            // user_id
            $table->unique('user_id', 'user_id_unique');

            // unique制約があればindexは不要なので削除
            $table->dropIndex('user_id_index');
        });

        ############################
        // レシートIDが重複しないことはストア別の話となるため、制約にplatformを含める
        // usr_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            // 今のusr_currency_paid_receipt_unique_id_unique制約を削除
            $table->dropUnique('receipt_unique_id_unique');

            // platform, receipt_unique_id
            $table->unique(['platform', 'receipt_unique_id'], 'platform_receipt_unique_id_unique');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        ############################
        // seq_noの削除
        // usr_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->dropUnique('user_id_seq_no_unique');
            $table->dropColumn('seq_no');

            // purchased_atの追加
            $table->timestampTz('purchased_at')->comment('購入日時')->after('platform');
            $table->index('purchased_at', 'purchased_at_index');
        });

        // log
        // log_store
        Schema::table(CurrencyDBUtility::getTableName('log_stores'), function (Blueprint $table) {
            $table->dropColumn('seq_no');
        });
        // log_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('log_currency_paids'), function (Blueprint $table) {
            $table->dropColumn('seq_no');
        });

        ############################
        // user_idのunique制約を削除する
        // usr_currency_summary
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_summaries'), function (Blueprint $table) {
            $table->dropUnique('user_id_unique');
            $table->index('user_id', 'user_id_index');
        });
        // usr_currency_free
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_frees'), function (Blueprint $table) {
            $table->dropUnique('user_id_unique');
            $table->index('user_id', 'user_id_index');
        });
        // usr_store_info
        Schema::table(CurrencyDBUtility::getTableName('usr_store_infos'), function (Blueprint $table) {
            $table->dropUnique('user_id_unique');
            $table->index('user_id', 'user_id_index');
        });

        ############################
        // receipt_unique_idのunique制約を戻す
        // usr_currency_paid
        Schema::table(CurrencyDBUtility::getTableName('usr_currency_paids'), function (Blueprint $table) {
            $table->dropUnique('platform_receipt_unique_id_unique');
            $table->unique('receipt_unique_id', 'receipt_unique_id_unique');
        });
    }
};
