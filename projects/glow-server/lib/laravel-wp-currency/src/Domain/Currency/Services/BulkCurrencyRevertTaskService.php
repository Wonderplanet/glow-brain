<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Services;

use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetFreeLog;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetPaidLog;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetFreeLogRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetPaidLogRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetRepository;
use WonderPlanet\Domain\Currency\Repositories\AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository;

/**
 * 一括通貨返却タスクのサービス
 */
class BulkCurrencyRevertTaskService
{
    // @codingStandardsIgnoreStart
    public function __construct(
        private readonly AdmBulkCurrencyRevertTaskRepository $admBulkCurrencyRevertTaskRepository,
        private readonly AdmBulkCurrencyRevertTaskTargetRepository $admBulkCurrencyRevertTaskTargetRepository,
        private readonly AdmBulkCurrencyRevertTaskTargetPaidLogRepository $admBulkCurrencyRevertTaskTargetPaidLogRepository,
        private readonly AdmBulkCurrencyRevertTaskTargetFreeLogRepository $admBulkCurrencyRevertTaskTargetFreeLogRepository,
        private readonly AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository $admBulkCurrencyRevertTaskTargetRevertHistoryLogRepository,
        private readonly CurrencyAdminService $currencyAdminService,
    ) {}
    // @codingStandardsIgnoreEnd

    /**
     * 一括通貨返却タスクを登録する
     *
     * 処理単位のタスクのみ登録する。
     * 処理するターゲットは別メソッドで登録する
     *
     * @param integer $admUserId
     * @param string $fileName
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param int $totalCount
     *
     * @return AdmBulkCurrencyRevertTask
     */
    public function registerTask(
        int $admUserId,
        string $fileName,
        int $revertCurrencyNum,
        string $comment,
        int $totalCount,
    ): AdmBulkCurrencyRevertTask {
        $task = $this->admBulkCurrencyRevertTaskRepository->create(
            $admUserId,
            $fileName,
            $revertCurrencyNum,
            $comment,
            $totalCount,
        );

        return $task;
    }

    /**
     * 一括通貨返却タスクの処理対象を登録する
     *
     * $dataBodyはキーと値の構造になっている
     * 読み込まれたデータファイルはデータのみになっているため、
     * メソッド呼び出し側でキーにマッピングされた状態で渡されることを想定している
     *
     * @param string $bulkCurrencyRevertTaskId
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param array<mixed> $dataBody 通貨返却対象データ
     * @param integer $chunkSize
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, AdmBulkCurrencyRevertTaskTarget> 登録したタスクと対象ユーザーリスト
     */
    public function registerTaskTargets(
        string $bulkCurrencyRevertTaskId,
        int $revertCurrencyNum,
        string $comment,
        array $dataBody,
        int $chunkSize = 1000
    ): EloquentCollection {
        // csvデータを登録
        // 登録するためのレコードデータに変換
        $targetRecords = [];
        $paidLogIdRecords = [];
        $freeLogIdRecords = [];
        $seqNo = 1;

        // 登録時刻を取得
        $targetNow = (new AdmBulkCurrencyRevertTaskTarget())->freshTimestamp()->toImmutable();
        $paidLogNow = (new AdmBulkCurrencyRevertTaskTargetPaidLog())->freshTimestamp()->toImmutable();
        $freeLogNow = (new AdmBulkCurrencyRevertTaskTargetFreeLog())->freshTimestamp()->toImmutable();
        foreach ($dataBody as $target) {
            $targetRecord = $this->admBulkCurrencyRevertTaskTargetRepository->makeRecordFromTargetData(
                $bulkCurrencyRevertTaskId,
                $revertCurrencyNum,
                $comment,
                $seqNo++,
                $targetNow,
                $target,
            );
            $taskTargetId = $targetRecord['id'];
            $targetRecords[] = $targetRecord;

            // log_currency_paid_idsが空でなければ登録
            $paidLogIdRecords = array_merge(
                $paidLogIdRecords,
                $this->admBulkCurrencyRevertTaskTargetPaidLogRepository->makeRecordsFromTargetData(
                    $taskTargetId,
                    $paidLogNow,
                    $target,
                )
            );

            // log_currency_free_idsが空でなければ登録
            $freeLogIdRecords = array_merge(
                $freeLogIdRecords,
                $this->admBulkCurrencyRevertTaskTargetFreeLogRepository->makeRecordsFromTargetData(
                    $taskTargetId,
                    $freeLogNow,
                    $target,
                )
            );
        }

        // データ登録
        $this->admBulkCurrencyRevertTaskTargetRepository->bulkInsert(
            $targetRecords,
            $chunkSize,
        );
        $this->admBulkCurrencyRevertTaskTargetPaidLogRepository->bulkInsert(
            $paidLogIdRecords,
            $chunkSize,
        );
        $this->admBulkCurrencyRevertTaskTargetFreeLogRepository->bulkInsert(
            $freeLogIdRecords,
            $chunkSize,
        );

        // 登録したデータをEloquentモデルで取得しなおすためselectする
        // データを取り直すことでパフォーマンスの懸念はあるが、
        // ローカル環境での確認だが、1万件の取得でも4.8秒程度だったので、管理ツールのジョブで使用するには問題ないと判断した
        $targets = $this->admBulkCurrencyRevertTaskTargetRepository
            ->findByBulkCurrencyRevertTaskId($bulkCurrencyRevertTaskId);

        return $targets;
    }

    /**
     * タスクを開始する
     *
     * @param AdmBulkCurrencyRevertTask $task
     * @return void
     */
    public function startTask(AdmBulkCurrencyRevertTask $task): void
    {
        $this->admBulkCurrencyRevertTaskRepository->updateStatusToProcessing($task->id);
    }

    /**
     * 対象データに対して通貨を返却する
     *
     * 返却した後で、関連して廃部処理を行う場合などは$afterRevertCallbackを設定します。
     * $afterRevertCallbackメソッドが動作し終わるとステータスがFinishedに変更されます。
     *
     * 配布完了後のメッセージ送信など、完全に配布が終わってからの処理は$finishedCallbackを設定します。
     *
     * エラー時には$errorCallbackが実行され、ステータスがErrorに変更されます。
     *
     * @param AdmBulkCurrencyRevertTaskTarget $target
     * @param integer $revertCurrencyNum
     * @param string $comment
     * @param Closure|null $beforeTargetRevertCallback 通貨返却前に実行する処理
     * @param Closure|null $afterTargetRevertCallback 通貨返却後に実行する処理
     * @param Closure|null $finishedTargetCallback 通貨返却処理が完了した際に実行する処理
     * @param Closure|null $errorTargetCallback 通貨返却処理がエラーになった際に実行する処理
     * @return array<string> 返却したLogCurrencyRevertHistoryのID
     * @throws \Throwable
     */
    public function revertCurrencyFromTarget(
        AdmBulkCurrencyRevertTaskTarget $target,
        int $revertCurrencyNum,
        string $comment,
        ?Closure $beforeTargetRevertCallback = null,
        ?Closure $afterTargetRevertCallback = null,
        ?Closure $finishedTargetCallback = null,
        ?Closure $errorTargetCallback = null,
    ): array {
        // 通貨返却処理
        try {
            // ステータスをProcessingに変更
            $this->admBulkCurrencyRevertTaskTargetRepository->updateStatusToProcessing($target->id);

            // 通貨返却前の処理
            if (!is_null($beforeTargetRevertCallback) && is_callable($beforeTargetRevertCallback)) {
                $beforeTargetRevertCallback($target, $revertCurrencyNum, $comment);
            }

            $historyLogIds = $this->currencyAdminService->revertCurrencyFromLog(
                $target->usr_user_id,
                $target->paidLogs()->pluck('log_currency_paid_id')->toArray(),
                $target->freeLogs()->pluck('log_currency_free_id')->toArray(),
                $comment,
                $revertCurrencyNum,
            );

            // 通貨返却後の処理
            if (!is_null($afterTargetRevertCallback) && is_callable($afterTargetRevertCallback)) {
                $afterTargetRevertCallback($target, $revertCurrencyNum, $comment, $historyLogIds);
            }

            // 返却ログをタスクに紐付けるログを記録
            $this->admBulkCurrencyRevertTaskTargetRevertHistoryLogRepository->bulkInsert(
                $target->id,
                $target->usr_user_id,
                $historyLogIds,
            );

            // ステータスを処理済みに変更
            $this->admBulkCurrencyRevertTaskTargetRepository->updateStatusToFinished($target->id);

            // 処理完了後の処理
            if (!is_null($finishedTargetCallback) && is_callable($finishedTargetCallback)) {
                $finishedTargetCallback($target, $revertCurrencyNum, $comment, $historyLogIds);
            }

            return $historyLogIds;
        } catch (\Throwable $e) {
            // Memo: メソッドをトランザクションで囲んでいる場合、ここもトランザクション内となるため、レコードの値を変更してもロールバックされる。
            //       ステータスを変更しても戻されてしまうので、エラーステータスへの変更は上位メソッドで行うこと

            // エラー時の処理
            if (!is_null($errorTargetCallback) && is_callable($errorTargetCallback)) {
                $errorTargetCallback($target, $revertCurrencyNum, $comment, $e);
            }

            throw $e;
        }
    }

    /**
     * 指定したIDの一括通貨返却タスクを終了させる
     *
     * @param string $taskId
     * @return void
     */
    public function finishBulkCurrencyRevertTask(string $taskId): void
    {
        // ターゲットの成功およびエラー件数を集計
        $successCount = $this->admBulkCurrencyRevertTaskTargetRepository
            ->countByStatus($taskId, AdmBulkCurrencyRevertTaskTargetStatus::Finished);
        $errorCount = $this->admBulkCurrencyRevertTaskTargetRepository
            ->countByStatus($taskId, AdmBulkCurrencyRevertTaskTargetStatus::Error);

        // タスクのステータスを更新
        $this->admBulkCurrencyRevertTaskRepository
            ->updateStatusToFinished($taskId, $successCount, $errorCount);
    }

    /**
     * タスクをエラーにする
     *
     * @param string $taskId
     * @param \Throwable $error
     * @return void
     */
    public function updateTaskToError(string $taskId, \Throwable $error): void
    {
        // ターゲットの成功およびエラー件数を集計
        $successCount = $this->admBulkCurrencyRevertTaskTargetRepository
            ->countByStatus($taskId, AdmBulkCurrencyRevertTaskTargetStatus::Finished);
        $errorCount = $this->admBulkCurrencyRevertTaskTargetRepository
            ->countByStatus($taskId, AdmBulkCurrencyRevertTaskTargetStatus::Error);

        // メッセージを作成
        $errorMessage = $this->makeJsonFromError($error);

        $this->admBulkCurrencyRevertTaskRepository
            ->updateStatusToError($taskId, $errorMessage, $successCount, $errorCount);
    }

    /**
     * 対象データをエラーにする
     *
     * @param string $targetId
     * @param \Throwable $error
     * @return void
     */
    public function updateTargetToError(string $targetId, \Throwable $error): void
    {
        // メッセージを作成
        $errorMessage = $this->makeJsonFromError($error);

        $this->admBulkCurrencyRevertTaskTargetRepository
            ->updateStatusToError($targetId, $errorMessage);
    }

    /**
     * エラーをJSON形式に変換する
     *
     * @param \Throwable $error
     * @return string
     */
    private function makeJsonFromError(\Throwable $error): string
    {
        return json_encode([
            'message' => $error->getMessage(),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ]);
    }
}
