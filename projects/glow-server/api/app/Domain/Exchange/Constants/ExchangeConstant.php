<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Constants;

class ExchangeConstant
{
    /** @var int 1回あたりの最大交換数 */
    public const MAX_TRADE_COUNT_PER_REQUEST = 100;
}
