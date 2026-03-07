<?php

declare(strict_types=1);

namespace App\Models\Log;

use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryPaidLog as BaseLogCurrencyRevertHistoryPaidLog;

class LogCurrencyRevertHistoryPaidLog extends BaseLogCurrencyRevertHistoryPaidLog
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
}
