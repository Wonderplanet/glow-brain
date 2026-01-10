<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetFreeLog;

/**
 * 一括通貨返却タスク対象の無償通貨ログリポジトリ
 */
class AdmBulkCurrencyRevertTaskTargetFreeLogRepository
{
    /**
     * 一括通貨返却タスク対象の無償通貨ログを一括登録する
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
            $result = AdmBulkCurrencyRevertTaskTargetFreeLog::insert($chunk);

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
        $freeLogIdRecords = [];

        // log_currency_free_idsが空でなければ登録
        if (!(is_null($target['log_currency_free_ids']) || $target['log_currency_free_ids'] === '')) {
            foreach (explode(',', $target['log_currency_free_ids']) as $logCurrencyFreeId) {
                if ($logCurrencyFreeId === '') {
                    continue;
                }

                $id = AdmBulkCurrencyRevertTaskTargetFreeLog::generateId();
                $freeLogIdRecords[] = [
                    'id' => $id,
                    'log_currency_free_id' => $logCurrencyFreeId,
                    'usr_user_id' => $target['usr_user_id'],
                    'adm_bulk_currency_revert_task_target_id' => $bulkCurrencyRevertTaskTargetId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }
        return $freeLogIdRecords;
    }
}
