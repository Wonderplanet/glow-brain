<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum RarityType: string
{
    case N      = 'N';
    case R      = 'R';
    case SR     = 'SR';
    case SSR    = 'SSR';
    case UR     = 'UR';
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

    public static function order(): Collection
    {
        return collect([
            self::N->value,
            self::R->value,
            self::SR->value,
            self::SSR->value,
            self::UR->value,
        ]);
    }
}
