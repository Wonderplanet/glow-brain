<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Exceptions;

use WonderPlanet\Domain\Currency\Constants\ErrorCode;
use WonderPlanet\Domain\Currency\Models\UsrCurrencySummary;

/**
 * 有償通貨が上限を超えている場合の例外
 */
class WpCurrencyAddPaidCurrencyOverByMaxException extends WpCurrencyException
{
    /**
     * コンストラクタ
     *
     * @param string $userId
     * @param integer $addAmount
     * @param integer $maxAmount
     * @param UsrCurrencySummary $summary
     */
    public function __construct(string $userId, int $addAmount, int $maxAmount, UsrCurrencySummary $summary)
    {
        $ownedAmount =
            "apple: {$summary->paid_amount_apple}, " .
            "google: {$summary->paid_amount_google}, " .
            "free: {$summary->free_amount}";
        parent::__construct(
            "{$userId} add paid currency over by max: add: {$addAmount}, max: {$maxAmount}, {$ownedAmount}",
            ErrorCode::ADD_PAID_CURRENCY_BY_OVER_MAX
        );
    }
}
