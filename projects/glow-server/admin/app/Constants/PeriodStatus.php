<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum PeriodStatus: string
{
    // 開催前
    case BEFORE = 'Before';
    // 開催中
    case DURING = 'During';
    // 終了
    case ENDED = 'Ended';

    public function label(): string
    {
        return match ($this) {
            self::BEFORE => '開催前',
            self::DURING => '開催中',
            self::ENDED => '終了',
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

    public function badgeColor(): string
    {
        return match ($this) {
            self::BEFORE => 'warning',
            self::DURING => 'success',
            self::ENDED => 'gray',
            default => 'gray',
        };
    }
}
