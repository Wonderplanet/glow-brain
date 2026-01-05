<?php

declare(strict_types=1);

namespace App\Models\Log;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistory as BaseLogCurrencyRevertHistory;

class LogCurrencyRevertHistory extends BaseLogCurrencyRevertHistory
{
    /**
     * Factoryクラスの取得 (デフォルトに戻す)
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        //
    }

    // リレーション
    /**
     * 返却を実行した時の有償一次通貨変動ログ
     *
     * @return HasManyThrough
     */
    public function paidLog(): HasManyThrough
    {
        return $this->HasManyThrough(
            LogCurrencyPaid::class,
            LogCurrencyRevertHistoryPaidLog::class,
            'log_currency_revert_history_id',
            'id',
            'id',
            'log_currency_paid_id'
        )->orderBy('seq_no', 'asc');
    }

    /**
     * 返却を実行した時の有償一次通貨変動ログID
     * IDには実行したときの変動ログと、返却対象としたログIDが含まれている
     *
     * @return HasMany
     */
    public function paidLogIds(): HasMany
    {
        return $this->HasMany(
            LogCurrencyRevertHistoryPaidLog::class,
            'log_currency_revert_history_id',
            'id'
        );
    }

    /**
     * 返却を実行した時の無償一次通貨変動ログ
     *
     * @return HasManyThrough
     */
    public function freeLog(): HasManyThrough
    {
        return $this->HasManyThrough(
            LogCurrencyFree::class,
            LogCurrencyRevertHistoryFreeLog::class,
            'log_currency_revert_history_id',
            'id',
            'id',
            'log_currency_free_id'
        );
    }

    /**
     * 返却を実行した時の無償一次通貨変動ログID
     * IDには実行したときの変動ログと、返却対象としたログIDが含まれている
     *
     * @return HasMany
     */
    public function freeLogIds(): HasMany
    {
        return $this->HasMany(
            LogCurrencyRevertHistoryFreeLog::class,
            'log_currency_revert_history_id',
            'id'
        );
    }
}
