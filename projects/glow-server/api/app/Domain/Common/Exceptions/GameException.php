<?php

declare(strict_types=1);

namespace App\Domain\Common\Exceptions;

class GameException extends \Exception
{
    public function __construct(int $code, string $message = '', ?\Throwable $previous = null)
    {
        // エラー文言はクライアント側で持つ想定なので省略可能にする
        parent::__construct($message, $code, $previous);
    }
}
