<?php

declare(strict_types=1);

namespace App\Domain\DailyBonus\Enums;

enum DailyBonusType: string
{
    case DAILY_BONUS = 'DailyBonus';
    case EVENT_DAILY_BONUS = 'EventDailyBonus';
    case COMEBACK_BONUS = 'ComebackBonus';
}
