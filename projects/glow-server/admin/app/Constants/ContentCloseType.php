<?php

declare(strict_types=1);

namespace App\Constants;
use Illuminate\Support\Collection;

enum ContentCloseType: string
{
    case ADVENT_BATTLE = 'AdventBattle';

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
