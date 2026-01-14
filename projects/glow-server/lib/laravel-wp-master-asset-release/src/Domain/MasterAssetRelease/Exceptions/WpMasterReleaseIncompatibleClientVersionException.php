<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Exceptions;

use WonderPlanet\Domain\MasterAssetRelease\Constants\ErrorCode;

/**
 * マスターリリース管理に関する例外
 * 指定したクライアントバージョンと互換性のある
 */
class WpMasterReleaseIncompatibleClientVersionException extends WpMasterReleaseException
{
    public function __construct(string $clientVersion)
    {
        parent::__construct(
            "Incompatible Client Version: $clientVersion",
            ErrorCode::INCOMPATIBLE_MASTER_DATA_FROM_CLIENT_VERSION
        );
    }
}
