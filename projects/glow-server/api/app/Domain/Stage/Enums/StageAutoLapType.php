<?php

declare(strict_types=1);

namespace App\Domain\Stage\Enums;

enum StageAutoLapType: string
{
    // ステージ一回クリアでスタミナブースト化
    case AFTER_CLEAR = 'AfterClear';

    // クリアせずとも最初からスタミナブースト化
    case INITIAL = 'Initial';
}
