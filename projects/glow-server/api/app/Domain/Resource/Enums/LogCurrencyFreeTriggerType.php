<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

/**
 * 無償通貨ログのトリガータイプ
 */
enum LogCurrencyFreeTriggerType: string
{
    case SHOP = 'shop';
    case MISSION = 'mission';
}
