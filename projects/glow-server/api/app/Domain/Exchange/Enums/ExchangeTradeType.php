<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Enums;

enum ExchangeTradeType: string
{
    case NORMAL_EXCHANGE_TRADE = 'NormalExchangeTrade';
    case EVENT_EXCHANGE_TRADE = 'EventExchangeTrade';
    case CHARACTER_FRAGMENT_EXCHANGE_TRADE = 'CharacterFragmentExchangeTrade';

    /**
     * 月次リセット対象かどうか
     */
    public function isMonthlyResetTarget(): bool
    {
        return $this === self::NORMAL_EXCHANGE_TRADE;
    }
}
