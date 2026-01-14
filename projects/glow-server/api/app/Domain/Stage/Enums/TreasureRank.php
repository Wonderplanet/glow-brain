<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

use Illuminate\Support\Collection;

enum TreasureRank: string
{
    case SSR = 'SSR';
    case SR = 'SR';
    case R = 'R';
    case N = 'N';
    case NONE = 'None';

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
