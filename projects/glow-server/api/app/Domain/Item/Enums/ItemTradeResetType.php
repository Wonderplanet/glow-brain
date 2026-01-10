<?php

declare(strict_types=1);

namespace App\Domain\Item\Enums;

enum ItemTradeResetType: string
{
    case NONE = 'None';
    case DAILY = 'Daily';
    case WEEKLY = 'Weekly';
    case MONTHLY = 'Monthly';
}
