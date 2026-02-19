<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Enums;

enum ExchangeCostType: string
{
    case COIN = 'Coin';
    case ITEM = 'Item';
}
