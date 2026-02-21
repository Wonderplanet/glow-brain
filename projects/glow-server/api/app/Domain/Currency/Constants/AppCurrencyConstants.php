<?php

declare(strict_types=1);

namespace App\Domain\Currency\Constants;

class AppCurrencyConstants
{
    // 消費タイプ
    /**
     * 有償一次通貨
     */
    public const CONSUME_TYPE_PAID = 'paid';

    /**
     * 有償・無償一次通貨
     */
    public const CONSUME_TYPE_CURRENCY = 'currency';
}
