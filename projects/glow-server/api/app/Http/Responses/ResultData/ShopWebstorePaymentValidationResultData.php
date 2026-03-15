<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

class ShopWebstorePaymentValidationResultData
{
    public function __construct(
        public string $transactionId,
    ) {
    }
}
