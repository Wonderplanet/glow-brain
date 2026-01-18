<?php

declare(strict_types=1);

namespace App\Models\Log;

use WonderPlanet\Domain\Currency\Models\LogCurrencyRevertHistoryFreeLog as BaseLogCurrencyRevertHistoryFreeLog;

class LogCurrencyRevertHistoryFreeLog extends BaseLogCurrencyRevertHistoryFreeLog
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
