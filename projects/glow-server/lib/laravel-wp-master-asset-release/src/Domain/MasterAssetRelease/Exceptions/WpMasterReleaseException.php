<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Exceptions;

/**
 * マスターリリース管理に関する例外
 */
class WpMasterReleaseException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        // この例外はフレームワーク側で発行されたことを示すため、メッセージに特定文字列を付与する
        $message = "Wp-Master-Release: {$message}";

        parent::__construct($message, $code, $previous);
    }
}
