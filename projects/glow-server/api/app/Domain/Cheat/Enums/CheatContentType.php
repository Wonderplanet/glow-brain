<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Enums;

enum CheatContentType: string
{
    // 降臨バトル
    case ADVENT_BATTLE = 'AdventBattle';
    // ランクマッチ
    case PVP = 'Pvp';
}
