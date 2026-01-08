<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetPaidLog;

/**
 * 一括通貨返却タスク対象の有償通貨ログリポジトリ
 */
class AdmBulkCurrencyRevertTaskTargetPaidLogRepository
{
    /**
     * 一括通貨返却タスク対象の有償通貨ログを一括登録する
     *
     * @param array<int, array<string, mixed>> $records
     * @param int $chunkSize
     * @return bool
     */
    public function bulkInsert(
        array $records,
        int $chunkSize = 1000,
    ): bool {
        $chunkedRecords = array_chunk($records, $chunkSize);

        foreach ($chunkedRecords as $chunk) {
            $result = AdmBulkCurrencyRevertTaskTargetPaidLog::insert($chunk);

            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * CSVのtargetデータからDBに登録するためのレコードを作成する
     *
     * @param string $bulkCurrencyRevertTaskTargetId
     * @param CarbonImmutable $now
     * @param array<string, mixed> $target
     * @return array<int, mixed>
     */
    public function makeRecordsFromTargetData(
        string $bulkCurrencyRevertTaskTargetId,
        CarbonImmutable $now,
        array $target
    ): array {
        $paidLogIdRecords = [];

        // log_currency_paid_idsが空でなければ登録
        if (!(is_null($target['log_currency_paid_ids']) || $target['log_currency_paid_ids'] === '')) {
            foreach (explode(',', $target['log_currency_paid_ids']) as $logCurrencyPaidId) {
                if ($logCurrencyPaidId === '') {
                    continue;
                }

                $id = AdmBulkCurrencyRevertTaskTargetPaidLog::generateId();
                $paidLogIdRecords[] = [
                    'id' => $id,
                    'adm_bulk_currency_revert_task_target_id' => $bulkCurrencyRevertTaskTargetId,
                    'usr_user_id' => $target['usr_user_id'],
                    'log_currency_paid_id' => $logCurrencyPaidId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        return $paidLogIdRecords;
    }
}
