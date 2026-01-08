<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Enums;

enum IdleIncentiveExecMethod: string
{
    case NORMAL = 'Normal';
    case QUICK_AD = 'QuickAd';
    case QUICK_DIAMOND = 'QuickDiamond';
}
