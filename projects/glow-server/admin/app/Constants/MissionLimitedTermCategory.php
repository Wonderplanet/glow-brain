<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionLimitedTermCategory as ApiMissionLimitedTermCategory;
use Illuminate\Support\Collection;

enum MissionLimitedTermCategory: string
{
    case ADVENT_BATTLE = ApiMissionLimitedTermCategory::ADVENT_BATTLE->value;

    public function label(): string
    {
        return match ($this) {
            self::ADVENT_BATTLE => '降臨バトル',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }
}
