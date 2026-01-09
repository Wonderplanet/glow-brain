<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod as ApiIdleIncentiveExecMethod;

enum IdleIncentiveExecMethod : string
{
    case NORMAL = ApiIdleIncentiveExecMethod::NORMAL->value;
    case QUICK_AD = ApiIdleIncentiveExecMethod::QUICK_AD->value;
    case QUICK_DIAMOND = ApiIdleIncentiveExecMethod::QUICK_DIAMOND->value;

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => '探索',
            self::QUICK_AD => 'クイック探索　広告',
            self::QUICK_DIAMOND => 'クイック探索　プリズム',
        };
    }
}
