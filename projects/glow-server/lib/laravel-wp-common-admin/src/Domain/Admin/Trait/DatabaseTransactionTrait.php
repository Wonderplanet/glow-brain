<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Admin\Trait;

use Illuminate\Support\Facades\DB;

/**
 * 管理画面処理でトランザクションを使うためのTrait
 *
 * DB::transactionだとデフォルトのコネクション(mysql)にしか対応してないため
 * apiにコネクションを貼ってトランザクションを有効にするために作成
 *
 * 中身は api/app/Domain/Common/Traits/UseCaseTrait.php のコピー
 */
trait DatabaseTransactionTrait
{
    /**
     * 指定したコネクションでトランザクションを開始し、コールバックを実行する
     *
     * 複数のコネクションを指定できるよう配列でとっているのは、
     * 水平/垂直分割を想定しているため
     *
     * @param callable $callback
     * @param array<string> $connections
     * @return mixed
     */
    public function transaction(callable $callback, array $connections = [])
    {
        // 指定が空の場合はデフォルトのコネクションを使う
        if (count($connections) === 0) {
            $connections = [config('database.default')];
        }

        // トランザクションの開始
        foreach ($connections as $connection) {
            DB::connection($connection)->beginTransaction();
        }

        // コールバックの実行
        try {
            $result = $callback();

            // トランザクションのコミット
            foreach ($connections as $connection) {
                DB::connection($connection)->commit();
            }

            return $result;
        } catch (\Throwable $e) {
            // 例外が発生した場合はロールバック
            foreach ($connections as $connection) {
                DB::connection($connection)->rollBack();
            }
            throw $e;
        }
    }
}
