<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Exceptions;

use WonderPlanet\Domain\Currency\Constants\ErrorCode;

/**
 * デバッグ機能が使用できない環境でAPIが実行された場合の例外
 */
class WpCurrencyInvalidDebugException extends WpCurrencyException
{
    public function __construct()
    {
        parent::__construct("Invalid environment. ", ErrorCode::INVALID_DEBUG_ENVIRONMENT);
    }
}
