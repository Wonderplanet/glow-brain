<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Exceptions;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;

/**
 * リクエストされた購入レシートがすでに処理済みだった場合の例外
 */
class WpBillingDuplicateReceiptException extends WpBillingException
{
    public function __construct(string $receiptUniqueId)
    {
        parent::__construct("Duplicate receipt unique id: {$receiptUniqueId}", ErrorCode::DUPLICATE_RECEIPT_UNIQUE_ID);
    }
}
