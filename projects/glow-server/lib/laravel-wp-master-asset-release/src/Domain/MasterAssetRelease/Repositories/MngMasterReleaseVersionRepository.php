<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;

class MngMasterReleaseVersionRepository
{
    /**
     * 配信中のMngMasterReleaseVersionEntityとclient_compatibility_versionのコレクションを取得する
     *
     * @param CarbonImmutable|null $now
     * @return Collection<int, mixed>
     */
    public function getApplyCollection(?CarbonImmutable $now = null): Collection
    {
        /** @var Collection<int, MngMasterReleaseVersion> $mngMasterReleaseVersions */
        $mngMasterReleaseVersions = MngMasterReleaseVersion::query()
            ->select('mng_master_release_versions.*', 'mng_master_releases.client_compatibility_version')
            ->join(
                'mng_master_releases',
                'mng_master_releases.target_release_version_id',
                '=',
                'mng_master_release_versions.id',
            )
            ->whereNotNull('mng_master_releases.target_release_version_id')
            ->where([
                'mng_master_releases.enabled' => 1,
            ])
            // start_atとend_atの期間条件を追加
            ->where(function ($query) use ($now) {
                $query->whereNull('mng_master_releases.start_at')
                    ->orWhere('mng_master_releases.start_at', '<=', $now);
            })
            ->orderBy('mng_master_releases.release_key', 'desc')
            ->limit(MasterData::MASTER_RELEASE_APPLY_LIMIT)
            ->get();

        // entityとclient_compatibility_versionを持つマップのコレクションを生成
        //   ※client_compatibility_versionはクエリでjoinしているため、クラスのプロパティとしては存在しない
        //   それが原因でphpstanのエラーが出るため、いったんエラーを抑制している
        // @phpstan-ignore-next-line
        return $mngMasterReleaseVersions
            // @phpstan-ignore-next-line
            ->map(fn ($version) => [
                'entity' => $version->toEntity(),
                'created_at' => $version->created_at,
                // @phpstan-ignore-next-line
                'client_compatibility_version' => $version->client_compatibility_version,
            ]);
    }
}
