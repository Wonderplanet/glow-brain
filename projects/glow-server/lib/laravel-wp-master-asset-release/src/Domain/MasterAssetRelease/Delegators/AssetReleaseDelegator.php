<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Delegators;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngAssetReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Services\AssetReleaseService;

/**
 * アセットリリースバージョン管理用Delegator
 */
readonly class AssetReleaseDelegator
{
    public function __construct(
        private readonly AssetReleaseService $assetReleaseService
    ) {
    }

    /**
     * 現在有効なアセット情報を取得
     *
     * @param int $platform
     * @param string $clientVersion
     * @return MngAssetReleaseVersionEntity|null
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpAssetReleaseApplyNotFoundException
     */
    public function getCurrentActiveAsset(
        int $platform,
        string $clientVersion,
        CarbonImmutable $now
    ): ?MngAssetReleaseVersionEntity {
        return $this->assetReleaseService->getCurrentActiveAsset($platform, $clientVersion, $now);
    }
}
