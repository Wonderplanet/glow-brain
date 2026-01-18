<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum AttackRangeType: string
{
    case SHORT  = 'Short';
    case MIDDLE = 'Middle';
    case LONG   = 'Long';
    case NONE   = 'None';

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            if ($case === self::NONE) {
                continue;
            }
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }
}
