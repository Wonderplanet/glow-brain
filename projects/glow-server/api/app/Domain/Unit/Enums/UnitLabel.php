<?php

declare(strict_types=1);

namespace App\Domain\Unit\Enums;

enum UnitLabel: string
{
    // Drop
    case DROP_R = 'DropR';
    case DROP_SR = 'DropSR';
    case DROP_SSR = 'DropSSR';
    case DROP_UR = 'DropUR';

    // Premium
    case PREMIUM_R = 'PremiumR';
    case PREMIUM_SR = 'PremiumSR';
    case PREMIUM_SSR = 'PremiumSSR';
    case PREMIUM_UR = 'PremiumUR';

    // Festival
    case FESTIVAL_UR = 'FestivalUR';
}
