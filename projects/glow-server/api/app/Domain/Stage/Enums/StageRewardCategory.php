<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum StageRewardCategory: string
{
    case ALWAYS = 'Always';
    case FIRST_CLEAR = 'FirstClear';
    case RANDOM = 'Random';
    case SPEED_ATTACK_CLEAR = 'SpeedAttackClear';
}
