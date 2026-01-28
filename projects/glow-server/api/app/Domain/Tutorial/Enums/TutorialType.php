<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Enums;

enum TutorialType: string
{
    // 導入パート
    case INTRO = 'Intro';

    // メインパート
    case MAIN = 'Main';

    // フリーパート
    case FREE = 'Free';
}
