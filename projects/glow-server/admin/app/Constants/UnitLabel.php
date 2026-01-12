<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum UnitLabel: string
{
    case DROP_R         = 'DropR';
    case DROP_SR        = 'DropSR';
    case DROP_SSR       = 'DropSSR';
    case PREMIUM_R      = 'PremiumR';
    case PREMIUM_SR     = 'PremiumSR';
    case PREMIUM_SSR    = 'PremiumSSR';
    case PREMIUM_UR     = 'PremiumUR';
    case FESTIVAL_UR    = 'FestivalUR';

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }
}
