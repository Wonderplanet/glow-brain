<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Enums;

enum PvpBonusType: string
{
    // 格上勝利
    case WinUpperBonus = 'WinUpperBonus';
    // 同格勝利
    case WinSameBonus = 'WinSameBonus';
    // 格下勝利
    case WinLowerBonus = 'WinLowerBonus';
    // クリアタイム
    case ClearTime = 'ClearTime';
}
