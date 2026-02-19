<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Delegators;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Services\MasterReleaseService;

/**
 * リリースバージョン管理用Delegator
 */
class MasterReleaseDelegator
{
    public function __construct(
        private readonly MasterReleaseService $masterReleaseService
    ) {
    }

    /**
     * 受け取ったクライアントバージョンを元に配信中のリリースバージョン情報のエンティティとクライアント互換性バージョンを取得する
     *
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return MngMasterReleaseVersionEntity
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion(
        string $clientVersion,
        CarbonImmutable $now,
    ): MngMasterReleaseVersionEntity {
        return $this->masterReleaseService->getApplyMasterReleaseVersionEntityByClientVersion($clientVersion, $now);
    }

    /**
     * 配信中のマスターリリースバージョン情報(client_compatibility_version含む)のコレクションを返す
     *
     * @return Collection
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException
     */
    public function getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection(
        CarbonImmutable $now
    ): Collection {
        return $this->masterReleaseService->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection(
            $now
        );
    }
}
