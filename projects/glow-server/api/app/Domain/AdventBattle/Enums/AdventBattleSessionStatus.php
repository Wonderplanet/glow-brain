<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Enums;

enum AdventBattleSessionStatus: int
{
    case CLOSED = 0;
    case STARTED = 1;
}
