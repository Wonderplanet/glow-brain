<?php

declare(strict_types=1);

namespace App\Domain\Outpost\Enums;

enum OutpostEnhancementType: string
{
    case LEADER_POINT_SPEED = 'LeaderPointSpeed';
    case LEADER_POINT_LIMIT = 'LeaderPointLimit';
    case OUTPOST_HP = 'OutpostHp';
    case SUMMON_INTERVAL = 'SummonInterval';
    case LEADER_POINT_UP = 'LeaderPointUp';
    case RUSH_CHARGE_SPEED = 'RushChargeSpeed';
}
