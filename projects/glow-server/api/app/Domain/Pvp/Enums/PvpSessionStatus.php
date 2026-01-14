<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Enums;

enum PvpSessionStatus: int
{
    case CLOSED = 0;
    case STARTED = 1;
}
