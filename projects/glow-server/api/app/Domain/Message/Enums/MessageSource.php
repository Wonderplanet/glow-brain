<?php

declare(strict_types=1);

namespace App\Domain\Message\Enums;

enum MessageSource: string
{
    // 運営メッセージ
    case MNG_MESSAGE = 'MngMessage';

    // リソース上限超過したので即時獲得できずメールボックスに送ったケース
    case RESOURCE_LIMIT_REACHED = 'ResourceLimitReached';
}
