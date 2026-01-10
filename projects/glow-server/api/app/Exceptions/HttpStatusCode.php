<?php

declare(strict_types=1);

namespace App\Exceptions;

class HttpStatusCode
{
    /** @var int 正常系 */
    const SUCCESS = 200;

    /** @var int 想定内のエラー */
    const ERROR = 299;
    
    /** @var int リトライで解消するかもしれないエラー */
    const RETRYABLE_ERROR = 503;
    
     /** @var int 不明なエラー */
    const UNKNOWN_ERROR = 500;
}
