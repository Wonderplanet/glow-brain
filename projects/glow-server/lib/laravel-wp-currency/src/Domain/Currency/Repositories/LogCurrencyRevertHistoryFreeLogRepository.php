<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryFreeLog;

/**
 * 無償一次通貨の返却を行なった際のログと通貨ログの紐付けを管理するリポジトリ
 */
class LogCurrencyRevertHistoryFreeLogRepository
{
    /**
     * ログを追加する
     *
     * @param string $userId
     * @param string $logCurrencyRevertHistoryId
     * @param string $logCurrencyFreeId
     * @param string $revertLogCurrencyFreeId
     * @return void
     */
    public function insertRevertHistoryFreeLog(
        string $userId,
        string $logCurrencyRevertHistoryId,
        string $logCurrencyFreeId,
        string $revertLogCurrencyFreeId,
    ): void {
        $logCurrencyRevert = new LogCurrencyRevertHistoryFreeLog();
        $logCurrencyRevert->usr_user_id = $userId;
        $logCurrencyRevert->log_currency_revert_history_id = $logCurrencyRevertHistoryId;
        $logCurrencyRevert->log_currency_free_id = $logCurrencyFreeId;
        $logCurrencyRevert->revert_log_currency_free_id = $revertLogCurrencyFreeId;
        $logCurrencyRevert->save();
    }
}
