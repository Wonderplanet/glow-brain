<?php

declare(strict_types=1);

namespace App\Domain\Common\Services;

/**
 * 遅延実行タスク管理サービス
 *
 * DBトランザクション完了後に実行したい処理を遅延実行するためのサービスです。
 * 主にDynamoDBの更新など、外部システムとの連携処理で使用します。
 *
 * 使用例：
 * ```php
 * // サービス内でタスクを登録
 * $this->deferredTaskService->registerAfterTransaction(function () {
 *     $this->dynRepository->updateStatus($data);
 * });
 *
 * // UseCaseTrait内で遅延タスクを実行
 * $this->applyUserTransactionChanges();
 * ```
 *
 * 実行タイミング：
 * 1. UseCaseでビジネスロジック実行
 * 2. DBトランザクションコミット
 * 3. 遅延タスク実行 ← ここで登録されたタスクが実行される
 * 4. ログ保存
 *
 * 注意事項：
 * - 遅延タスクで例外が発生してもDBトランザクションはロールバックされません
 * - タスクの実行順序は登録順です
 * - 1リクエスト内でのみ有効（リクエストスコープ）
 */
class DeferredTaskService
{
    /**
     * DBトランザクション終了後に実行されるタスクのリスト
     * @var callable[]
     */
    private array $afterTransactionTasks = [];

    /**
     * DBトランザクション終了後に実行されるタスクを登録
     */
    public function registerAfterTransaction(callable $task): void
    {
        $this->afterTransactionTasks[] = $task;
    }

    /**
     * DBトランザクション終了後のタスクを実行
     */
    public function executeAfterTransactionTasks(): void
    {
        foreach ($this->afterTransactionTasks as $task) {
            $task();
        }
        $this->afterTransactionTasks = [];
    }
}
