<?php

declare(strict_types=1);

namespace App\Constants;

use App\Traits\EnumTrait;

enum TutorialFunctionName: string
{
    use EnumTrait;

    case NOT_PLAYED = 'NotPlayed';

    public function label(): string
    {
        return match ($this) {
            self::NOT_PLAYED => '未プレイ',
        };
    }
}
