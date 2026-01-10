<?php

declare(strict_types=1);

namespace App\Domain\Shop\Constants;

class ShopConstant
{
    public const IDLE_INCENTIVE_COIN_RESOURCE_TYPE = "IdleCoin";
    public const ON_TIME_PASS_REPURCHASE_DAYS = 3;
    public const DAY_TIME_SECONDS = 86400;

    // 受け取り期限日数
    public const DAILY_PASS_REWARD_RECEIVE_DEADLINE = 60;
    public const IMMEDIATELY_PASS_REWARD_RECEIVE_DEADLINE = 60;

    public const DEFAULT_SHOP_PASS_NAME = 'パス購入';
}
