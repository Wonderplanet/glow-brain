<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Constants\MasterData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngMasterReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngMasterReleaseVersionRepository as BaseRepository;

class MngMasterReleaseVersionRepository extends BaseRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * 配信中のMngMasterReleaseVersionEntityとclient_compatibility_versionのコレクションを取得する
     *
     * @param CarbonImmutable|null $now
     * @return Collection
     */
    public function getApplyCollection(?CarbonImmutable $now = null): Collection
    {
        $dataFetcher = function () use ($now) {
            $cacheBaseTime = $this->mngCacheRepository->getCacheBaseTime($now);

            // ベースクエリ
            $query = MngMasterReleaseVersion::query()
                ->select(
                    'mng_master_release_versions.*',
                    'mng_master_releases.client_compatibility_version',
                    'mng_master_releases.start_at',
                )
                ->join(
                    'mng_master_releases',
                    'mng_master_releases.target_release_version_id',
                    '=',
                    'mng_master_release_versions.id',
                )
                ->whereNotNull('mng_master_releases.target_release_version_id')
                ->where([
                    'mng_master_releases.enabled' => 1,
                ]);

            // 現在有効なリリースデータを指定個数分取得
            /** @var Collection $mngMasterReleaseVersions */
            $mngMasterReleaseVersions = (clone $query)
                ->where(function ($query) use ($cacheBaseTime) {
                    $query->whereNull('mng_master_releases.start_at')
                        ->orWhere('mng_master_releases.start_at', '<=', $cacheBaseTime);
                })
                ->orderBy('mng_master_releases.release_key', 'desc')
                ->limit(MasterData::MASTER_RELEASE_APPLY_LIMIT)
                ->get();

            // 未来のリリースデータを全て取得
            // データ取得タイミングで有効かどうかが変動するため全て取得しておく
            $futureMngMasterReleaseVersions = (clone $query)
                ->where(function ($query) use ($cacheBaseTime) {
                    $query->Where('mng_master_releases.start_at', '>', $cacheBaseTime);
                })
                ->get();

            // 現在有効なものと未来のリリースデータの両方を返す
            return $mngMasterReleaseVersions->merge($futureMngMasterReleaseVersions);
        };

        // 取得したバージョン情報をキャッシュに保存
        /**
         * @var Collection<MngMasterReleaseVersion> $mngMasterReleaseVersions
         */
        $mngMasterReleaseVersions = $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngMasterReleaseVersionKey(),
            $dataFetcher,
        );

        $mngMasterReleaseVersions = $mngMasterReleaseVersions
            ->filter(function (MngMasterReleaseVersion $version) use ($now) {
                // 元々のmng_master_release_versionsにはstart_atはないのでphpstanエラーが出るが
                // このモデルインスタンスがこのメソッド外に出ることはないので無視する
                /** @phpstan-ignore-next-line */
                return is_null($version->start_at) || $version->start_at <= $now;
            })
            ->sortByDesc('release_key')
            ->take(MasterData::MASTER_RELEASE_APPLY_LIMIT);

        // entityとclient_compatibility_versionを持つマップのコレクションを生成
        return $mngMasterReleaseVersions
            ->map(fn ($version) => [
                'entity' => $version->toEntity(),
                'client_compatibility_version' => $version->client_compatibility_version,
            ]);
    }

    /**
     * キャッシュを削除する
     */
    public function deleteAllCache(): void
    {
        $this->mngCacheRepository->deleteCache(CacheKeyUtil::getMngMasterReleaseVersionKey());
    }
}
