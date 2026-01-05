<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Billing\Exceptions;

/**
 * 課金基盤に関連する例外
 */
class WpBillingException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        // この例外はフレームワーク側で発行されたことを示すため、メッセージに特定文字列とエラーコードを付与する
        $message = "Billing-{$code}: {$message}";

        parent::__construct($message, $code, $previous);
    }
}
