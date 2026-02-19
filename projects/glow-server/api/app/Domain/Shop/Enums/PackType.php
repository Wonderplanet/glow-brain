<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum PackType: string
{
    // ステージクリア
    case NORMAL = 'Normal';

    // ユーザーレベル
    case DAILY = 'Daily';
}
