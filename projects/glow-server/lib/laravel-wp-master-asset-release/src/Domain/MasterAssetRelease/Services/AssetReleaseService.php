<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Services;

use Carbon\CarbonImmutable;
use WonderPlanet\Domain\MasterAssetRelease\Entities\MngAssetReleaseVersionEntity;
use WonderPlanet\Domain\MasterAssetRelease\Exceptions\WpAssetReleaseApplyNotFoundException;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngAssetReleaseVersionRepository;
use WonderPlanet\Domain\MasterAssetRelease\Traits\ReleaseVersionTrait;

readonly class AssetReleaseService
{
    use ReleaseVersionTrait;

    public function __construct(
        private readonly MngAssetReleaseVersionRepository $mngAssetReleaseVersionRepository,
    ) {
    }

    /**
     * 現在有効なアセット情報を取得
     *
     * @param int $platform
     * @param string $clientVersion
     * @param CarbonImmutable $now
     * @return MngAssetReleaseVersionEntity|null
     * @throws WpAssetReleaseApplyNotFoundException
     */
    public function getCurrentActiveAsset(
        int $platform,
        string $clientVersion,
        CarbonImmutable $now,
    ): ?MngAssetReleaseVersionEntity {
        // 指定プラットフォームの配信中のアセットリリース情報を取得
        $mngAssetReleaseVersions = $this->mngAssetReleaseVersionRepository->getApplyCollection($platform, $now);

        if ($mngAssetReleaseVersions->isEmpty()) {
            // 配信中のリリース情報がない
            throw new WpAssetReleaseApplyNotFoundException($platform);
        }

        $enableReleaseVersions = [];
        foreach ($mngAssetReleaseVersions as $assetReleaseVersion) {
            $versionEntity = $assetReleaseVersion['entity'];
            $mngClientVersion = $assetReleaseVersion['client_compatibility_version'];

            // クライアントバージョンが互換性のあるバージョンよりも古い場合はスキップ
            if (version_compare($clientVersion, $mngClientVersion, '<')) {
                continue;
            }

            // まだ登録されていなければリリースデータをセット
            $currentVersion = $enableReleaseVersions[$versionEntity->getReleaseKey()] ?? null;
            if (is_null($currentVersion)) {
                $enableReleaseVersions[$versionEntity->getReleaseKey()] = $assetReleaseVersion;
                continue;
            }

            // すでにセットされていればどちらを優先してリリースするか比較してリセット
            $latestVersion = $this->getLatestByVersion(
                $currentVersion,
                $assetReleaseVersion
            );
            $enableReleaseVersions[$versionEntity->getReleaseKey()] = $latestVersion;
        }

        // クライアントバージョンと互換性のあるリリース情報がない場合はnullを返す
        if ($enableReleaseVersions === []) {
            return null;
        }

        // entityデータだけに整形
        $entityMap = array_map(function ($value) {
            return $value['entity'];
        }, $enableReleaseVersions);

        // リリースキーで降順ソートして先頭(一番最新)のentityを返す
        krsort($entityMap);
        return array_shift($entityMap);
    }
}
