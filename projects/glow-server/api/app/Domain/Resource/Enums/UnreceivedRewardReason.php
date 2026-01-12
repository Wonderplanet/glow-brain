<?php

declare(strict_types=1);

namespace App\Domain\Resource\Enums;

enum UnreceivedRewardReason: string
{
    // 受け取れた状態。受け取れなかった理由はなし
    case NONE = 'None';

    // 報酬獲得時にリソース上限超過したため、受け取りできなかった
    case RESOURCE_LIMIT_REACHED = 'ResourceLimitReached';

    // 報酬獲得時にリソース上限超過したため、上限までは受け取ったが、上限を超えた分は破棄された
    case RESOURCE_OVERFLOW_DISCARDED = 'ResourceOverflowDiscarded';

    // 不正なデータで、受け取りできなかった
    case INVALID_DATA = 'InvalidData';

    // 報酬獲得時にリソース上限超過したため、即時受け取りは行わず、メールボックスに送った
    case SENT_TO_MESSAGE = 'SentToMessage';
}
