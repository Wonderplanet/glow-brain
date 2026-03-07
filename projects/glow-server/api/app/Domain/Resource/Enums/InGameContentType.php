<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum InGameContentType: string
{
    case STAGE = 'Stage';
    case ADVENT_BATTLE = 'AdventBattle';
    case PVP = 'Pvp';
}
