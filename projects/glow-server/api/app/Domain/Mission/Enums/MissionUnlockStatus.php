<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionUnlockStatus: int
{
    // 未解放状態
    case LOCK = 0;

    // 開放済
    case OPEN = 1;
}
