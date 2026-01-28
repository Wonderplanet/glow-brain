<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum RoleType: string
{
    case ATTACK     = 'Attack';
    case BALANCE    = 'Balance';
    case DEFENSE    = 'Defense';
    case SUPPORT    = 'Support';
    case UNIQUE     = 'Unique';
    case NONE       = 'None';
    case TECHNICAL  = 'Technical';
    case SPECIAL    = 'Special';

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
