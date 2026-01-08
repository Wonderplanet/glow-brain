<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Currency\Exceptions;

/**
 * 通貨管理操作に関連する例外
 */
class WpCurrencyException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        // この例外はフレームワーク側で発行されたことを示すため、メッセージに特定文字列とエラーコードを付与する
        $message = "Currency-{$code}: {$message}";

        parent::__construct($message, $code, $previous);
    }
}
