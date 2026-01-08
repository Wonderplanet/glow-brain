<?php

declare(strict_types=1);

namespace App\Domain\Resource\Log\Enums;

enum BankKPIF001EventId: string
{
    /** ユーザー登録 */
    case USER_REGISTERED = '100';
    /** ユーザー無効(リセマラ) */
    case USER_DISABLED = '200';
    /** プレイ継続中(1時間単位) */
    case ACTIVE = '300';
}
