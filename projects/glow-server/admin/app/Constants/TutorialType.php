<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Tutorial\Enums\TutorialType as BaseTutorialType;
use App\Traits\EnumTrait;

enum TutorialType: string
{
    use EnumTrait;

    case INTRO = BaseTutorialType::INTRO->value;
    case MAIN = BaseTutorialType::MAIN->value;
    case FREE = BaseTutorialType::FREE->value;

    public function label(): string
    {
        return match ($this) {
            self::INTRO => '導入パート',
            self::MAIN => 'メインパート',
            self::FREE => 'フリーパート',
        };
    }
}
