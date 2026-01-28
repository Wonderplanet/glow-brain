<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\Cache\Constants;

/**
 * 共通基盤で使用するエラーコードを定義するクラス
 */
class ErrorCode
{
    /** @var int マスターDBの接続先とreleaseControlの接続先情報が異なる */
    public const MASTER_DATABASE_CONNECTIONS_DIFFERENT = 99005;
}
