<?php

declare(strict_types=1);

namespace App\Domain\Shop\Constants;

class WebStoreConstant
{
    /** WebStoreトランザクションステータス */
    public const TRANSACTION_STATUS_PENDING = 'pending';
    public const TRANSACTION_STATUS_COMPLETED = 'completed';
    public const TRANSACTION_STATUS_FAILED = 'failed';

    /** @var int この年齢以上は、購入制限なし */
    public const PURCHASE_ALLOWED_AGE = 18;

    public const SANDBOX = 'sandbox';
}
