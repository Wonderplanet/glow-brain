<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Stage\Enums\StageResetType as ApiResetType;

enum ResetType: string
{
    case DAILY = ApiResetType::DAILY->value;

    public function label(): string
    {
        return match ($this) {
            self::DAILY => '日跨ぎリセット',
        };
    }
}
