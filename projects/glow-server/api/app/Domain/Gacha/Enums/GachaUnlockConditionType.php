<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Enums;

enum GachaUnlockConditionType: string
{
    case NONE = 'None';
    case MAIN_PART_TUTORIAL_COMPLETE = 'MainPartTutorialComplete';
}
