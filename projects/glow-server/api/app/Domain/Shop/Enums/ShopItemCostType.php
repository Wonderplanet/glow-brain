<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum ShopItemCostType: string
{
    // コイン(2次通貨)
    case COIN = 'Coin';
    // 有償一次通貨
    case PAID_DIAMOND = 'PaidDiamond';
    // 一次通貨(無償→有償の順で消費)
    case DIAMOND = 'Diamond';
    // 広告
    case AD = 'Ad';
    // 無料
    case FREE = 'Free';
}
