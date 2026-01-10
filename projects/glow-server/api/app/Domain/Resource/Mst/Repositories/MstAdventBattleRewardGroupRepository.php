<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity as Entity;
use App\Domain\Resource\Mst\Models\MstAdventBattleRewardGroup as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstAdventBattleRewardGroupRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getMapAll(): Collection
    {
        return $this->getAll()->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @param string $adventBattleId
     * @param string $adventBattleRewardCategory
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByAdventBattleIdAndCategory(
        string $adventBattleId,
        string $adventBattleRewardCategory
    ): Collection {
        return $this->getAll()->filter(function ($entity) use ($adventBattleId, $adventBattleRewardCategory) {
            /** @var Entity $entity */
            return $entity->getMstAdventBattleId() === $adventBattleId &&
                $entity->getRewardCategory() === $adventBattleRewardCategory;
        });
    }
}
