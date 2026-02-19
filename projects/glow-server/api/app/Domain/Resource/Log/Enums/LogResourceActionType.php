<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Enums;

enum LogResourceActionType: string
{
    // リソースを獲得
    case GET = 'Get';

    // リソースを消費
    case USE = 'Use';
}
