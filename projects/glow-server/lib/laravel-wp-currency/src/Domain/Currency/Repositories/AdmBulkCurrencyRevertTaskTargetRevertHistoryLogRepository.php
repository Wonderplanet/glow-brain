<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\AdmBulkCurrencyRevertTaskTargetRevertHistoryLog;

/**
 * 一次通貨の一括返却タスクを実行した際の返却履歴ログのリポジトリ
 */
class AdmBulkCurrencyRevertTaskTargetRevertHistoryLogRepository
{
    /**
     * 一括処理タスクの対象ユーザー情報の返却履歴ログを一括登録する
     *
     * @param string $admBulkCurrencyRevertTaskTargetId
     * @param string $usrUserId
     * @param array<string> $logCurrencyRevertHistoryIds
     * @return boolean
     */
    public function bulkInsert(
        string $admBulkCurrencyRevertTaskTargetId,
        string $usrUserId,
        array $logCurrencyRevertHistoryIds
    ): bool {
        $records = [];
        $now = (new AdmBulkCurrencyRevertTaskTargetRevertHistoryLog())->freshTimestamp()->toImmutable();
        foreach ($logCurrencyRevertHistoryIds as $logCurrencyRevertHistoryId) {
            $records[] = [
                'id' => AdmBulkCurrencyRevertTaskTargetRevertHistoryLog::generateId(),
                'adm_bulk_currency_revert_task_target_id' => $admBulkCurrencyRevertTaskTargetId,
                'usr_user_id' => $usrUserId,
                'log_currency_revert_history_id' => $logCurrencyRevertHistoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        return AdmBulkCurrencyRevertTaskTargetRevertHistoryLog::insert($records);
    }
}
