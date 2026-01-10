<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

use WonderPlanet\Domain\Currency\Entities\Trigger;

/**
 * 有償通貨ログのトリガータイプ
 */
enum LogCurrencyPaidTriggerType: string
{
    case SHOP = 'ShopPurchasedReward';
    case GACHA = 'gacha';
    case CONSUMPTION = 'glow';
    case REFUND = Trigger::TRIGGER_TYPE_COLLECT_CURRENCY_PAID_ADMIN;
}
