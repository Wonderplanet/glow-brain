<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

/**
 * ショップログのトリガータイプ
 */
enum LogStoreTriggerType: string
{
    case SHOP = 'ShopPurchasedReward';
}
