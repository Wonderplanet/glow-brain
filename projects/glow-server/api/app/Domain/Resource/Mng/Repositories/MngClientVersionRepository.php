<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngClientVersionEntity;
use App\Domain\Resource\Mng\Models\MngClientVersion;
use App\Infrastructure\MngCacheRepository;
use Illuminate\Support\Collection;
use WonderPlanet\Domain\Common\Constants\PlatformConstant;

readonly class MngClientVersionRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    public function findByVersion(string $clientVersion, int $platform): ?MngClientVersionEntity
    {
        $mngClientVersions = $this->getMngClientVersionsByPlatform($platform);

        return $mngClientVersions->get($clientVersion, null);
    }

    private function getMngClientVersionsByPlatform(int $platform): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngClientVersionKey($platform),
            fn() => $this->createMngClientVersionsByPlatform($platform),
        );
    }

    /**
     * 指定プラットフォームのMngClientVersionEntityを作成
     *
     * @param int $platform
     * @return Collection<string, MngClientVersionEntity>
     *   key: client_version, value: MngClientVersionEntity
     */
    private function createMngClientVersionsByPlatform(int $platform): Collection
    {
        $result = collect();
        $models = MngClientVersion::query()
            ->where('platform', $platform)
            ->get();

        foreach ($models as $model) {
            $entity = $model->toEntity();
            $result->put($entity->getClientVersion(), $entity);
        }

        return $result;
    }

    public function deleteAllCache(): void
    {
        $platforms = [PlatformConstant::PLATFORM_IOS, PlatformConstant::PLATFORM_ANDROID];

        foreach ($platforms as $platform) {
            $this->mngCacheRepository->deleteCache(
                CacheKeyUtil::getMngClientVersionKey($platform)
            );
        }
    }
}
