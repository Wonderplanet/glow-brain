<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Constants\AssetData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;

class MngAssetReleaseVersionRepository
{
    /**
     * 指定プラットフォームの配信中のMngAssetReleaseVersionEntityとclient_compatibility_versionのコレクションを取得する
     *
     * @param int $platform
     * @param CarbonImmutable $now
     * @return Collection<int, mixed>
     */
    public function getApplyCollection(int $platform, CarbonImmutable $now): Collection
    {
        /** @var Collection<int, MngAssetReleaseVersion> $mngAssetReleaseVersions */
        $mngAssetReleaseVersions = MngAssetReleaseVersion::query()
            ->select(
                'mng_asset_release_versions.*',
                'mng_asset_releases.platform',
                'mng_asset_releases.client_compatibility_version',
            )
            ->join(
                'mng_asset_releases',
                'mng_asset_releases.target_release_version_id',
                '=',
                'mng_asset_release_versions.id'
            )
            // mng_asset_releasesから、現在有効なアセット情報を特定する
            ->whereNotNull('mng_asset_releases.target_release_version_id')
            ->where([
                'mng_asset_releases.platform' => $platform,
                'mng_asset_releases.enabled' => true,
            ])
            // start_atとend_atの期間条件を追加
            ->where(function ($query) use ($now) {
                $query->whereNull('mng_asset_releases.start_at')
                    ->orWhere('mng_asset_releases.start_at', '<=', $now);
            })
            ->orderBy('mng_asset_releases.release_key', 'desc')
            ->limit(AssetData::ASSET_RELEASE_APPLY_LIMIT)
            ->get();

        // entityとclient_compatibility_versionを持つマップのコレクションを生成
        //   ※client_compatibility_versionはクエリでjoinしているため、クラスのプロパティとしては存在しない
        //   それが原因でphpstanのエラーが出るため、いったんエラーを抑制している
        // @phpstan-ignore-next-line
        return $mngAssetReleaseVersions
            // @phpstan-ignore-next-line
            ->map(fn ($version) => [
                'entity' => $version->toEntity(),
                'created_at' => $version->created_at,
                // @phpstan-ignore-next-line
                'client_compatibility_version' => $version->client_compatibility_version,
            ]);
    }
}
