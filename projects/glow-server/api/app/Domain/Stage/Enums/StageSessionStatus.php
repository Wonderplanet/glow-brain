<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum StageSessionStatus: int
{
    case CLOSED = 0;
    case STARTED = 1;
}
