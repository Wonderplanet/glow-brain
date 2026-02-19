<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Exceptions;

use WonderPlanet\Domain\Common\Constants\PlatformConstant;
use WonderPlanet\Domain\MasterAssetRelease\Constants\ErrorCode;

/**
 * アセットリリース管理に関する例外
 * 配信中のリリース情報が存在しない
 */
class WpAssetReleaseApplyNotFoundException extends WpAssetReleaseException
{
    public function __construct(int $platform)
    {
        $platformStr = PlatformConstant::PLATFORM_STRING_LIST[$platform];
        parent::__construct(
            "Not Found Apply Release Platform: $platformStr",
            ErrorCode::NOT_FOUND_APPLY_ASSET_RELEASE
        );
    }
}
