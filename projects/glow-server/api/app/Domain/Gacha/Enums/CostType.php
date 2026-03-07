<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum CostType: string
{
    case DIAMOND = 'Diamond';
    case PAID_DIAMOND = 'PaidDiamond';
    case FREE = 'Free';
    case ITEM = 'Item';
    case AD = 'Ad';
}
