<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionBeginnerStatus: int
{
    // 未開放あり
    case HAS_LOCKED = 0;

    // 全て開放済
    case FULLY_UNLOCKED = 1;

    // 完了済(全クリア かつ 全報酬受取済)
    case COMPLETED = 2;
}
