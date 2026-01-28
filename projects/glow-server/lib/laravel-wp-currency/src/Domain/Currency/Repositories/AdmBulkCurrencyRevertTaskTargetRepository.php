<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;
use WonderPlanet\Domain\Currency\Enums\AdmBulkCurrencyRevertTaskTargetStatus;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTarget;

/**
 * 一括通貨返却タスク対象リポジトリ
 *
 * 返却する対象になる情報を一件づつ管理する
 */
class AdmBulkCurrencyRevertTaskTargetRepository
{
    /**
     * 複数の対象ユーザー情報を一括登録する
     *
     * @param array<int, array<string, string>> $records
     * @param integer $chunkSize
     *
     * @return bool
     */
    public function bulkInsert(
        array $records,
        int $chunkSize = 1000
    ): bool {
        // $recordsを1000件ずつ分割してinsertする
        $chunkedRecords = array_chunk($records, $chunkSize);
        foreach ($chunkedRecords as $chunk) {
            $result = AdmBulkCurrencyRevertTaskTarget::insert($chunk);

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * CSVのtargetデータからDBに登録するためのレコードを作成する
     *
     * created_at, updated_atは呼び出し元で設定する
     *
     * @param string $bulkCurrencyRevertTaskId
     * @param int $revertCurrencyNum
     * @param string $comment
     * @param int $seqNo
     * @param CarbonImmutable $now
     * @param array<string, string> $target
     *
     * @return array<string, mixed>
     */
    public function makeRecordFromTargetData(
        string $bulkCurrencyRevertTaskId,
        int $revertCurrencyNum,
        string $comment,
        int $seqNo,
        CarbonImmutable $now,
        array $target,
    ): array {
        $id = AdmBulkCurrencyRevertTaskTarget::generateId();

        return [
            'id' => $id,
            'adm_bulk_currency_revert_task_id' => $bulkCurrencyRevertTaskId,
            'seq_no' => $seqNo,
            'usr_user_id' => $target['usr_user_id'],
            'status' => AdmBulkCurrencyRevertTaskTargetStatus::Ready,
            'revert_currency_num' => $revertCurrencyNum,
            'consumed_at' => CarbonImmutable::parse($target['consumed_at'])
                ->setTimezone(CurrencyConstants::DATABASE_TZ),
            'trigger_type' => $target['trigger_type'],
            'trigger_id' => $target['trigger_id'],
            'trigger_name' => $target['trigger_name'],
            'request_id' => $target['request_id'],
            'sum_log_change_amount_paid' => $target['sum_log_change_amount_paid'],
            'sum_log_change_amount_free' => $target['sum_log_change_amount_free'],
            'comment' => $comment,
            'error_message' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    /**
     * 指定したbulk_currency_revert_task_idに紐づく対象ユーザー情報を取得する
     *
     * @param string $bulkCurrencyRevertTaskId
     * @return Collection<int, AdmBulkCurrencyRevertTaskTarget>
     */
    public function findByBulkCurrencyRevertTaskId(string $bulkCurrencyRevertTaskId): Collection
    {
        return AdmBulkCurrencyRevertTaskTarget::query()
            ->with(['paidLogs', 'freeLogs'])
            ->where('adm_bulk_currency_revert_task_id', $bulkCurrencyRevertTaskId)
            ->orderBy('seq_no')
            ->get();
    }

    /**
     * 対象ユーザー情報のステータスをProcessingに更新する
     *
     * @param string $bulkCurrencyRevertTaskTargetId
     * @return bool
     */
    public function updateStatusToProcessing(
        string $bulkCurrencyRevertTaskTargetId
    ): bool {
        return $this->updateStates(
            $bulkCurrencyRevertTaskTargetId,
            AdmBulkCurrencyRevertTaskTargetStatus::Processing
        );
    }

    /**
     * 対象ユーザー情報のステータスをFinishedに更新する
     *
     * @param string $bulkCurrencyRevertTaskTargetId
     * @return bool
     */
    public function updateStatusToFinished(
        string $bulkCurrencyRevertTaskTargetId
    ): bool {
        return $this->updateStates(
            $bulkCurrencyRevertTaskTargetId,
            AdmBulkCurrencyRevertTaskTargetStatus::Finished
        );
    }

    /**
     * 対象ユーザー情報のステータスをErrorに更新する
     *
     * @param string $bulkCurrencyRevertTaskTargetId
     * @param string $errorMessage
     * @return boolean
     */
    public function updateStatusToError(
        string $bulkCurrencyRevertTaskTargetId,
        string $errorMessage = '',
    ): bool {
        return $this->updateStates(
            $bulkCurrencyRevertTaskTargetId,
            AdmBulkCurrencyRevertTaskTargetStatus::Error,
            ['error_message' => $errorMessage]
        );
    }

    /**
     * 対象ユーザー情報のステータスを更新する
     *
     * @param string $bulkCurrencyRevertTaskTargetId
     * @param AdmBulkCurrencyRevertTaskTargetStatus $status
     * @param array<string, mixed> $params 追加で更新する必要のあるカラム
     * @return bool
     */
    private function updateStates(
        string $bulkCurrencyRevertTaskTargetId,
        AdmBulkCurrencyRevertTaskTargetStatus $status,
        array $params = [],
    ): bool {
        $num = AdmBulkCurrencyRevertTaskTarget::where('id', $bulkCurrencyRevertTaskTargetId)
            ->update(array_merge(['status' => $status], $params));

        return $num === 1;
    }

    /**
     * 対象ユーザー情報の指定されたステータス件数を集計する
     *
     * @param string $bulkCurrencyRevertTaskId
     * @param AdmBulkCurrencyRevertTaskTargetStatus $status
     * @return int
     */
    public function countByStatus(string $bulkCurrencyRevertTaskId, AdmBulkCurrencyRevertTaskTargetStatus $status): int
    {
        return AdmBulkCurrencyRevertTaskTarget::where('adm_bulk_currency_revert_task_id', $bulkCurrencyRevertTaskId)
            ->where('status', $status)
            ->count();
    }
}
