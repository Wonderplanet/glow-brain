<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Models\MngDeletedMyId;
use App\Infrastructure\MngCacheRepository;
use Illuminate\Support\Collection;

readonly class MngDeletedMyIdRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    public function findByMyId(string $myId): ?string
    {
        $mngDeletedMyIds = $this->getMngDeletedMyIds();

        return $mngDeletedMyIds->get($myId, null);
    }

    public function existsByMyId(string $myId): bool
    {
        return $this->findByMyId($myId) !== null;
    }

    private function getMngDeletedMyIds(): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngDeletedMyIdKey(),
            fn() => $this->createMngDeletedMyIds(),
        );
    }

    /**
     * MngDeletedMyIdEntityを作成
     *
     * @return Collection<string, string>
     *   key: my_id, value: my_id
     */
    private function createMngDeletedMyIds(): Collection
    {
        $result = collect();
        $models = MngDeletedMyId::query()->get();

        foreach ($models as $model) {
            $result->put($model->getMyId(), $model->getMyId());
        }

        return $result;
    }

    public function deleteAllCache(): void
    {
        $this->mngCacheRepository->deleteCache(
            CacheKeyUtil::getMngDeletedMyIdKey()
        );
    }
}
