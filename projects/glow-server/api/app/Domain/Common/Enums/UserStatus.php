<?php

declare(strict_types=1);

namespace App\Domain\Common\Enums;

/**
 * usr_users.statusの値を定義するEnum
 */
enum UserStatus: int
{
    // 通常プレイ可（デフォルト）
    case NORMAL = 0;

    // 返金対応中
    case REFUNDING = 5;

    // 時限BAN（一時的な利用停止状態。遊べる可能性がまだある）
    // 不正行為による、時限BAN
    case BAN_TEMPORARY_CHEATING = 10;
    // 異常なデータを検出したことによる、時限BAN
    case BAN_TEMPORARY_DETECTED_ANOMALY = 11;

    // 永久BAN（永久的な利用停止状態。二度と同アカウントで遊べない）
    case BAN_PERMANENT = 20;

    // アカウント削除対応による削除済みユーザー（永久的な利用停止状態。二度と同アカウントで遊べない）
    case DELETED = 30;
}
