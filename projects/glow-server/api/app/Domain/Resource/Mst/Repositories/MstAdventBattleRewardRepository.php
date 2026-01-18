<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstAdventBattleReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstAdventBattleRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<Entity>
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @return Collection<Entity>
     */
    public function getByGroupId(string $groupId): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupId) {
            /** @var Entity $entity */
            return $entity->getMstAdventBattleRewardGroupId() === $groupId;
        });
    }

    /**
     * @return Collection<Entity>
     */
    public function getByGroupIds(Collection $groupIds): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupIds) {
            /** @var Entity $entity */
            return $groupIds->contains($entity->getMstAdventBattleRewardGroupId());
        })->sortBy(function ($entity) use ($groupIds) {
            /** @var Entity $entity */
            return $groupIds->search($entity->getMstAdventBattleRewardGroupId());
        })->values();
    }
}
