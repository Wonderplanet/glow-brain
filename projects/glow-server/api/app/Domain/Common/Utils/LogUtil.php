<?php

declare(strict_types=1);

namespace App\Domain\Common\Utils;

class LogUtil
{
    public static function getNginxRequestId(): string
    {
        return $_SERVER['REQUEST_ID'] ?? '';
    }

    public static function getRequestId(): string
    {
        // TODO: 後で詳細を実装する
        return '';
    }
}
