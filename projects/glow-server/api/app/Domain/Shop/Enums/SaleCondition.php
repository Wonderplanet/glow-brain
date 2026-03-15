<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum SaleCondition: string
{
    // ステージクリア
    case STAGE_CLEAR = 'StageClear';

    // ユーザーレベル
    case USER_LEVEL = 'UserLevel';
}
