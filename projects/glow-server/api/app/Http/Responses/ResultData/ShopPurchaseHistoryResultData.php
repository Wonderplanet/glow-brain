<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class ShopPurchaseHistoryResultData
{
    /**
     * @param Collection<\App\Domain\Shop\Entities\CurrencyPurchase> $currencyPurchases
     */
    public function __construct(
        public Collection $currencyPurchases,
    ) {
    }
}
