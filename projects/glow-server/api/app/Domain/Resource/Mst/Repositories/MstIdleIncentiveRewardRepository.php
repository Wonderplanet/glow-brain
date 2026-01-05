<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstIdleIncentiveRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstIdleIncentiveReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstIdleIncentiveRewardRepository
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
     * @param  Collection<string>  $ids
     * @return Collection<Entity>
     */
    public function getByIds(Collection $ids): Collection
    {
        $entities = $this->getAll()->filter(function ($entity) use ($ids) {
            return $ids->containsStrict($entity->getId());
        });
        return $entities->values();
    }

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_idle_incentive_rewards record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @param  Collection<string>  $ids
     * @return Collection<Entity>
     */
    public function getMapById(Collection $ids): Collection
    {
        return $this->getByIds($ids)->keyBy(function (Entity $entity): string {
            return $entity->getId();
        });
    }

    /**
     * @param string $mstStageId
     * @return Entity|null
     */
    public function getByMstStageId(string $mstStageId): ?Entity
    {
        return $this->getAll()->filter(function ($entity) use ($mstStageId) {
            return $entity->getMstStageId() === $mstStageId;
        })->first();
    }
}
