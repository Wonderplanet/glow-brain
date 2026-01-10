<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\User\Constants\UserConstant;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\MasterAssetRelease\Constants\AssetData;
use WonderPlanet\Domain\MasterAssetRelease\Models\MngAssetReleaseVersion;
use WonderPlanet\Domain\MasterAssetRelease\Repositories\MngAssetReleaseVersionRepository as BaseRepository;

class MngAssetReleaseVersionRepository extends BaseRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * 指定プラットフォームの配信中のMngAssetReleaseVersionEntityとclient_compatibility_versionのコレクションを取得する
     *
     * @param int $platform
     * @param CarbonImmutable $now
     * @return Collection
     */
    public function getApplyCollection(int $platform, CarbonImmutable $now): Collection
    {
        $dataFetcher = function () use ($platform, $now) {
            $cacheBaseTime = $this->mngCacheRepository->getCacheBaseTime($now);

            // ベースクエリ
            $query = MngAssetReleaseVersion::query()
                ->select(
                    'mng_asset_release_versions.*',
                    'mng_asset_releases.platform',
                    'mng_asset_releases.client_compatibility_version',
                    'mng_asset_releases.start_at',
                )
                ->join(
                    'mng_asset_releases',
                    'mng_asset_releases.target_release_version_id',
                    '=',
                    'mng_asset_release_versions.id'
                )
                ->whereNotNull('mng_asset_releases.target_release_version_id')
                ->where([
                    'mng_asset_releases.platform' => $platform,
                    'mng_asset_releases.enabled' => true,
                ]);

            // 現在有効なリリースデータを指定個数分取得
            /** @var Collection $mngAssetReleaseVersions */
            $mngAssetReleaseVersions = (clone $query)
                ->where(function ($query) use ($cacheBaseTime) {
                    $query->whereNull('mng_asset_releases.start_at')
                        ->orWhere('mng_asset_releases.start_at', '<=', $cacheBaseTime);
                })
                ->orderBy('mng_asset_releases.release_key', 'desc')
                ->limit(AssetData::ASSET_RELEASE_APPLY_LIMIT)
                ->get();

            // 未来のリリースデータを全て取得
            // データ取得タイミングで有効かどうかが変動するため全て取得しておく
            $futureMngAssetReleaseVersions = (clone $query)
                ->where(function ($query) use ($cacheBaseTime) {
                    $query->Where('mng_asset_releases.start_at', '>', $cacheBaseTime);
                })
                ->get();

            // 現在有効なものと未来のリリースデータの両方を返す
            return $mngAssetReleaseVersions->merge($futureMngAssetReleaseVersions);
        };

        // 取得したバージョン情報をキャッシュに保存
        /**
         * @var Collection<MngAssetReleaseVersion> $mngAssetReleaseVersions
         */
        $mngAssetReleaseVersions = $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngAssetReleaseVersionKey($platform),
            $dataFetcher,
        );

        $mngAssetReleaseVersions = $mngAssetReleaseVersions
            ->filter(function (MngAssetReleaseVersion $version) use ($now) {
                // 元々のmng_asset_release_versionsにはstart_atはないのでphpstanエラーが出るが
                // このモデルインスタンスがこのメソッド外に出ることはないので無視する
                /** @phpstan-ignore-next-line */
                return is_null($version->start_at) || $version->start_at <= $now;
            })
            ->sortByDesc('release_key')
            ->take(AssetData::ASSET_RELEASE_APPLY_LIMIT);

        // entityとclient_compatibility_versionを持つマップのコレクションを生成
        return $mngAssetReleaseVersions
            ->map(fn ($version) => [
                'entity' => $version->toEntity(),
                'client_compatibility_version' => $version->client_compatibility_version,
            ]);
    }

    /**
     * @return Collection<string>
     */
    private function getAllCacheKeys(): Collection
    {
        $keys = collect();
        $platforms = array_keys(UserConstant::PLATFORM_STRING_LIST);
        foreach ($platforms as $platform) {
            $keys->push(CacheKeyUtil::getMngAssetReleaseVersionKey($platform));
        }
        return $keys;
    }

    /**
     * 全プラットフォームのキャッシュを削除する
     */
    public function deleteAllCache(): void
    {
        foreach ($this->getAllCacheKeys() as $key) {
            $this->mngCacheRepository->deleteCache($key);
        }
    }
}
