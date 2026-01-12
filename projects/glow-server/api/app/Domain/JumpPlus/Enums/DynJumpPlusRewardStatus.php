<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Enums;

enum DynJumpPlusRewardStatus: int
{
    case NOT_RECEIVED = 0;
    case RECEIVED = 1;
}
