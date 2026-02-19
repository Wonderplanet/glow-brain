<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum ProductType: string
{
    // ダイヤモンド
    case DIAMOND = 'diamond';

    // パック
    case PACK = 'pack';

    // パス
    case PASS = 'pass';
}
