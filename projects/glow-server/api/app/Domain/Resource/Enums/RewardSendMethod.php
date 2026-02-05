<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum RewardSendMethod: string
{
    // 未指定。各報酬タイプごとのSendServiceのsend処理に任せる
    case NONE = 'None';

    // 上限チェックして超過したらメッセージへ送信
    case SEND_TO_MESSAGE = 'SendToMessage';

    // 上限チェックをして超過したらエラーを出す
    case THROW_ERROR_WHEN_RESOURCE_LIMIT_REACHED = 'ThrowErrorWhenResourceLimitReached';
}
