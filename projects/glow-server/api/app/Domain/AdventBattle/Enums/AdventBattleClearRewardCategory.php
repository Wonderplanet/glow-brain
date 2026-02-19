<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Enums;

enum AdventBattleClearRewardCategory: string
{
    case ALWAYS = 'Always';
    case FIRST_CLEAR = 'FirstClear';
    case RANDOM = 'Random';
    case DROP = 'Drop';
}
