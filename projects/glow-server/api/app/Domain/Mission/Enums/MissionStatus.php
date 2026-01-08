<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionStatus: int
{
    case UNCLEAR = 0;
    case CLEAR = 1;
    case RECEIVED_REWARD = 2;
}
