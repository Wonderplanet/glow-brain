<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngMasterReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngMasterReleaseVersionRepository;
use WonderPlanet\Domain\MasterAssetRelease\Traits\ReleaseVersionTrait;

class MasterReleaseService
{
    use ReleaseVersionTrait;

    /**
     * @var Collection<int, mixed>|null
     */
    private ?Collection $masterReleaseVersionCollection = null;

    public function __construct(
        private readonly MngMasterReleaseVersionRepository $mngMasterReleaseVersionRepository,
    ) {
        $this->masterReleaseVersionCollection = collect();
    }

    /**
     * 受け取ったクライアントバージョンを元に配信中のリリースバージョン情報のエンティティとクライアント互換性バージョンを取得する
     *
     * @param string $clientVersion
     * @return MngMasterReleaseVersionEntity
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseIncompatibleClientVersionException
     */
    public function getApplyMasterReleaseVersionEntityByClientVersion(
        string $clientVersion,
        CarbonImmutable $now,
    ): MngMasterReleaseVersionEntity {
        // 現在配信中のリリース情報を取得
        $masterReleaseVersions = $this->getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection($now);

        $enableReleaseVersions = [];
        foreach ($masterReleaseVersions as $masterReleaseVersion) {
            $versionEntity = $masterReleaseVersion['entity'];
            $mngClientVersion = $masterReleaseVersion['client_compatibility_version'];

            // クライアントバージョンが互換性のあるバージョンよりも古い場合はスキップ
            if (version_compare($clientVersion, $mngClientVersion, '<')) {
                continue;
            }

            // まだ登録されていなければリリースデータをセット
            $currentVersion = $enableReleaseVersions[$versionEntity->getReleaseKey()] ?? null;
            if (is_null($currentVersion)) {
                $enableReleaseVersions[$versionEntity->getReleaseKey()] = $masterReleaseVersion;
                continue;
            }

            // すでにセットされていればどちらを優先してリリースするか比較してリセット
            $latestVersion = $this->getLatestByVersion(
                $currentVersion,
                $masterReleaseVersion
            );
            $enableReleaseVersions[$versionEntity->getReleaseKey()] = $latestVersion;
        }

        // クライアントバージョンと互換性のあるリリース情報がない場合はnullを返す
        if ($enableReleaseVersions === []) {
            throw new WpMasterReleaseIncompatibleClientVersionException($clientVersion);
        }

        // entityデータだけに整形
        $entityMap = array_map(function ($value) {
            return $value['entity'];
        }, $enableReleaseVersions);

        // リリースキーで降順ソートして先頭(一番最新)のentityを返す
        krsort($entityMap);
        return array_shift($entityMap);
    }

    /**
     * 配信中のマスターリリースバージョン情報(client_compatibility_version含む)のコレクションを返す
     *
     * @param CarbonImmutable $now
     * @return Collection<int, mixed>
     * @throws \WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpMasterReleaseApplyNotFoundException
     */
    public function getApplyMasterReleaseVersionEntityAndClientCompatibilityVersionCollection(
        CarbonImmutable $now
    ): Collection {
        if (!is_null($this->masterReleaseVersionCollection) && $this->masterReleaseVersionCollection->isNotEmpty()) {
            return $this->masterReleaseVersionCollection;
        }

        $collection = $this->mngMasterReleaseVersionRepository
            ->getApplyCollection($now);

        if ($collection->isEmpty()) {
            // 配信中のリリース情報がない
            throw new WpMasterReleaseApplyNotFoundException();
        }

        return $collection;
    }
}
