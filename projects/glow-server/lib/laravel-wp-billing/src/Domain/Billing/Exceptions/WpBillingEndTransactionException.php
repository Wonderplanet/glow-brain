<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Exceptions;

use WonderPlanet\Domain\Billing\Constants\ErrorCode;

/**
 * トランザクション終了専用例外
 */
class WpBillingEndTransactionException extends WpBillingException
{
    public function __construct()
    {
        parent::__construct("Exception handling that ends a transaction", ErrorCode::BILLING_TRANSACTION_END);
    }
}
