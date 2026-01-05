<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum PassRewardType: string
{
    case DAILY = 'Daily';
    case IMMEDIATELY = 'Immediately';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => '毎日報酬',
            self::IMMEDIATELY => '即時報酬',
        };
    }
}
