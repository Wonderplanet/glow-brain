<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Repositories;

use Illuminate\Support\Carbon;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog;

/**
 * 有償一次通貨の返却を行なった際のログと通貨ログの紐付けを管理するリポジトリ
 */
class LogCurrencyRevertHistoryPaidLogRepository
{
    /**
     * ログを追加する
     *
     * @param string $userId
     * @param string $logCurrencyRevertHistoryId
     * @param string $logCurrencyPaidId
     * @param string $revertLogCurrencyPaidId
     * @return void
     */
    public function insertRevertHistoryPaidLog(
        string $userId,
        string $logCurrencyRevertHistoryId,
        string $logCurrencyPaidId,
        string $revertLogCurrencyPaidId,
    ): void {
        $logCurrencyRevert = new LogCurrencyRevertHistoryPaidLog();
        $logCurrencyRevert->usr_user_id = $userId;
        $logCurrencyRevert->log_currency_revert_history_id = $logCurrencyRevertHistoryId;
        $logCurrencyRevert->log_currency_paid_id = $logCurrencyPaidId;
        $logCurrencyRevert->revert_log_currency_paid_id = $revertLogCurrencyPaidId;
        $logCurrencyRevert->save();
    }

    /**
     * 返却対象のlog_currency_paidのidを全て取得
     *
     * @param Carbon $startAt メソッド内でUTCとして扱う
     * @return array<int, array<string, string>>
     */
    public function getRevertLogCurrencyPaidIdsByStartAt(Carbon $startAt): array
    {
        // startAtをUTCとして扱う
        //  元のオブジェクトのTZが変わらないようにcloneする
        $startAtUtc = $startAt->clone()->utc();

        return LogCurrencyRevertHistoryPaidLog::query()
            ->select('revert_log_currency_paid_id', 'log_currency_paid_id')
            ->where('created_at', '>=', $startAtUtc)
            ->get()
            ->toArray();
    }
}
