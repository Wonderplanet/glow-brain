<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionLimitedTermCategory: string
{
    case ADVENT_BATTLE = 'AdventBattle';
    case ARTWORK_PANEL = 'ArtworkPanel';
}
