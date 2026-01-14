<?php

declare(strict_types=1);

namespace App\Domain\Shop\Constants;

class ShopPurchaseHistoryConstant
{
    public const HISTORY_LIMIT = 50;

    public const HISTORY_DAYS = 7;

    /**
     * 通貨フォーマット用ロケール
     */
    public const NUMBER_FORMATTER_LOCAL = 'ja_JP';
}
