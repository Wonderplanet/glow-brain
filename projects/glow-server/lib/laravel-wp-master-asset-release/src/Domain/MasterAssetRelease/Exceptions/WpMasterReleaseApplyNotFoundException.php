<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Exceptions;

use WonderPlanet\Domain\MasterAssetRelease\Constants\ErrorCode;

/**
 * マスターリリース管理に関する例外
 * 配信中のリリース情報が存在しない
 */
class WpMasterReleaseApplyNotFoundException extends WpMasterReleaseException
{
    public function __construct()
    {
        parent::__construct("Not Found Apply Release", ErrorCode::NOT_FOUND_APPLY_MASTER_RELEASE);
    }
}
