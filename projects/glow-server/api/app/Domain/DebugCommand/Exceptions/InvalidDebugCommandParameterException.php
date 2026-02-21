<?php

declare(strict_types=1);

namespace App\Domain\DebugCommand\Exceptions;

use Exception;

/**
 * デバッグコマンドのパラメータが不正な場合にスローされる例外
 */
class InvalidDebugCommandParameterException extends Exception
{
}
