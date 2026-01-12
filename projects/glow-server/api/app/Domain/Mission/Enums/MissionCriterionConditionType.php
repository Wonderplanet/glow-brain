<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

enum MissionCriterionConditionType: string
{
    // 開放条件として使う
    case UNLOCK = 'Unlock';

    // 達成条件として使う
    case CLEAR = 'Clear';
}
