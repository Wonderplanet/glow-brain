<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Enums;

enum PvpMatchingType: string
{
    // 格上
    case Upper = 'Upper';
    // 同格
    case Same = 'Same';
    // 格下
    case Lower = 'Lower';

    // 初期値
    case None = 'None';

    case Unavailable = 'Unavailable';
}
