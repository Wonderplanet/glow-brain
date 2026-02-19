<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 注意: このマイグレーションは、テーブル名とインデックス名を全体的に置き換える特殊対応をしている。
 * 課金ライブラリで定義しているデフォルトのテーブル名があった場合に置き換えを行なっているため、
 * wp_currencyのテーブル定義が新しくなっている場合、このマイグレーションファイルの後に続くロールバックなどで他のマイグレーションが失敗する可能性がある。
 *
 * そのためdownは意図的にコメントアウトしている。
 *
 * テーブル名を過去のものに戻したい場合は、次の手順を取ること
 * 1. donwのコメントを外し、このマイグレーションのみをロールバックする
 * 2. wp_currencyのテーブル名の設定を戻す
 */

return new class extends Migration
{
    /**
     * 変換テーブル一覧
     * upおよびdownで使用する
     *
     * @var array
     */
    private array $tables = [
        // mst
        'mst_store_product' => 'mst_store_products',
        // opr
        'opr_product' => 'opr_products',
        // usr
        'usr_currency_summary' => 'usr_currency_summaries',
        'usr_currency_free' => 'usr_currency_frees',
        'usr_currency_paid' => 'usr_currency_paids',
        'usr_store_info' => 'usr_store_infos',
        'usr_store_allowance' => 'usr_store_allowances',
        'usr_store_product_history' => 'usr_store_product_histories',
        // log
        'log_store' => 'log_stores',
        'log_currency_paid' => 'log_currency_paids',
        'log_currency_free' => 'log_currency_frees',
        'log_currency_cash' => 'log_currency_cashes',
        'log_allowance' => 'log_allowances',
    ];

    /**
     * インデックス変換テーブル一覧
     *
     * upおよびdonwで使用する
     *
     * 新しい設定で生成される場合も考慮して、テーブル名が新しいものも対象にする。
     * (インデックスがない場合は無視される)
     *
     * 新しいインデックス名はテーブル名を取り除いたものにする。
     * テーブル名もプロダクトによって変わるため
     *
     * @var array
     */
    private array $indexes = [
        // mst
        'mst_store_products' => [
            'mst_store_product_product_id_ios_index' => 'product_id_ios_index',
            'mst_store_product_product_id_android_index' => 'product_id_android_index',

            // 新テーブル対応
            'mst_store_products_product_id_ios_index' => 'product_id_ios_index',
            'mst_store_products_product_id_android_index' => 'product_id_android_index',
        ],
        // opr
        'opr_products' => [
            'opr_product_mst_store_product_id_index' => 'mst_store_product_id_index',

            // 新テーブル対応
            'opr_products_mst_store_product_id_index' => 'mst_store_product_id_index',
        ],
        // usr
        'usr_currency_summaries' => [
            'usr_currency_summary_user_id_unique' => 'user_id_unique',

            // 新テーブル対応
            'usr_currency_summaries_user_id_unique' => 'user_id_unique',
        ],
        'usr_currency_frees' => [
            'usr_currency_free_user_id_unique' => 'user_id_unique',

            // 新テーブル対応
            'usr_currency_frees_user_id_unique' => 'user_id_unique',
        ],
        'usr_currency_paids' => [
            'usr_currency_paid_user_id_seq_no_unique' => 'user_id_seq_no_unique',
            'usr_currency_paid_platform_receipt_unique_id_unique' => 'platform_receipt_unique_id_unique',
            'usr_currency_paid_user_id_index' => 'user_id_index',

            // 新テーブル対応
            'usr_currency_paids_user_id_seq_no_unique' => 'user_id_seq_no_unique',
            'usr_currency_paids_platform_receipt_unique_id_unique' => 'platform_receipt_unique_id_unique',
            'usr_currency_paids_user_id_index' => 'user_id_index',
        ],
        'usr_store_infos' => [
            'usr_store_info_user_id_unique' => 'user_id_unique',

            // 新テーブル対応
            'usr_store_infos_user_id_unique' => 'user_id_unique',
        ],
        'usr_store_allowances' => [
            'usr_store_allowance_user_id_platform_product_id_unique' => 'user_id_platform_product_id_unique',
            'usr_store_allowance_user_id_index' => 'user_id_index',

            // 新テーブル対応
            'usr_store_allowances_user_id_platform_product_id_unique' => 'user_id_platform_product_id_unique',
            'usr_store_allowances_user_id_index' => 'user_id_index',
        ],
        'usr_store_product_histories' => [
            'usr_store_product_history_user_id_index' => 'user_id_index',

            // 新テーブル対応
            'usr_store_product_histories_user_id_index' => 'user_id_index',
        ],
        // log
        'log_stores' => [
            'log_store_user_id_index' => 'user_id_index',
            'log_store_opr_product_id_index' => 'opr_product_id_index',

            // 新テーブル対応
            'log_stores_user_id_index' => 'user_id_index',
            'log_stores_opr_product_id_index' => 'opr_product_id_index',
        ],
        'log_currency_paids' => [
            'log_currency_paid_user_id_index' => 'user_id_index',
            'log_currency_paid_currency_paid_id_index' => 'currency_paid_id_index',

            // 新テーブル対応
            'log_currency_paids_user_id_index' => 'user_id_index',
            'log_currency_paids_currency_paid_id_index' => 'currency_paid_id_index',
        ],
        'log_currency_frees' => [
            'log_currency_free_user_id_index' => 'user_id_index',
            'log_currency_free_created_at_index' => 'created_at_index',

            // 新テーブル対応
            'log_currency_frees_user_id_index' => 'user_id_index',
            'log_currency_frees_created_at_index' => 'created_at_index',
        ],
        'log_currency_cashes' => [
            'log_currency_cash_user_id_index' => 'user_id_index',
            'log_currency_cash_created_at_index' => 'created_at_index',

            // 新テーブル対応
            'log_currency_cashes_user_id_index' => 'user_id_index',
            'log_currency_cashes_created_at_index' => 'created_at_index',
        ],
        'log_allowances' => [
            'log_allowance_user_id_index' => 'user_id_index',

            // 新テーブル対応
            'log_allowances_user_id_index' => 'user_id_index',
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // テーブル名を複数形に変更するため、課金基盤のデフォルト名でつけられたテーブルが存在していたらリネームする
        foreach ($this->tables as $tableName => $newTableName) {
            if (Schema::hasTable($tableName)) {
                Schema::rename($tableName, $newTableName);
            }
        }

        // インデックス名の補正
        foreach ($this->indexes as $newTableName => $indexes) {
            foreach ($indexes as $indexName => $newIndexName) {
                $this->renameIndex($newTableName, $indexName, $newIndexName);
            }
        }
    }

    private function renameIndex(string $table, string $indexName, string $newIndexName)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        if (!isset($indexes[$indexName])) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName, $newIndexName) {
            $table->renameIndex($indexName, $newIndexName);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // リネームされたテーブルを戻す
        // foreach ($this->tables as $tablename => $newname) {
        //     if (Schema::hasTable($newname)) {
        //         Schema::rename($newname, $tablename);
        //     }
        // }

        // インデックス名は、過去のマイグレーション含めてテーブル名を除去しているため戻さない。
        // このマイグレーションではテーブル名が含まれたインデックスがあった場合に、テーブル名を含まないように補正しているため
        // 戻さなくても整合性は取れるはず
    }
};
