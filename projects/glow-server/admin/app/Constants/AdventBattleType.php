<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;
use App\Domain\AdventBattle\Enums\AdventBattleType as ApiAdventBattleType;

enum AdventBattleType: string
{
    case SCORE_CHALLENGE = ApiAdventBattleType::SCORE_CHALLENGE->value;
    case RAID = ApiAdventBattleType::RAID->value;

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
