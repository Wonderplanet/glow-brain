<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTask;

/**
 * 一次通貨返却タスクのリポジトリ
 */
class AdmBulkCurrencyRevertTaskRepository
{
    /**
     * 初期登録状態のタスクを作成する
     *
     * @param int $admUserId
     * @param string $fileName
     * @param int $revertCurrencyNum
     * @param string $comment
     * @param int $totalCount
     * @return AdmBulkCurrencyRevertTask
     */
    public function create(
        int $admUserId,
        string $fileName,
        int $revertCurrencyNum,
        string $comment,
        int $totalCount,
    ): AdmBulkCurrencyRevertTask {
        $status = AdmBulkCurrencyRevertTaskStatus::Ready;
        $successCount = 0;
        $errorCount = 0;

        $model = new AdmBulkCurrencyRevertTask();
        $model->adm_user_id = $admUserId;
        $model->file_name = $fileName;
        $model->revert_currency_num = $revertCurrencyNum;
        $model->comment = $comment;
        $model->status = $status;
        $model->total_count = $totalCount;
        $model->success_count = $successCount;
        $model->error_count = $errorCount;
        $model->error_message = '';
        $model->save();

        return $model;
    }

    /**
     * タスクのステータスを処理中に更新する
     *
     * @param string $id
     * @return void
     */
    public function updateStatusToProcessing(string $id): void
    {
        $this->updateStatus($id, AdmBulkCurrencyRevertTaskStatus::Processing);
    }

    /**
     * タスクのステータスを完了に更新する
     *
     * @param string $id
     * @return void
     */
    public function updateStatusToFinished(string $id, int $success, int $error): void
    {
        $this->updateStatus($id, AdmBulkCurrencyRevertTaskStatus::Finished, [
            'success_count' => $success,
            'error_count' => $error,
        ]);
    }

    /**
     * タスクのステータスをエラーに更新する
     *
     * @param string $id
     * @param string $errorMessage
     * @param int $success
     * @param int $error
     * @return void
     */
    public function updateStatusToError(string $id, string $errorMessage, int $success, int $error): void
    {
        $this->updateStatus($id, AdmBulkCurrencyRevertTaskStatus::Error, [
            'error_message' => $errorMessage,
            'success_count' => $success,
            'error_count' => $error,
        ]);
    }

    /**
     * タスクのステータスを更新する
     *
     * @param string $id
     * @param AdmBulkCurrencyRevertTaskStatus $status
     * @param array<string, mixed> $params
     * @return void
     */
    private function updateStatus(
        string $id,
        AdmBulkCurrencyRevertTaskStatus $status,
        array $params = [],
    ): void {
        AdmBulkCurrencyRevertTask::query()
            ->where('id', $id)
            ->update(array_merge([
                'status' => $status,
            ], $params));
    }
}
