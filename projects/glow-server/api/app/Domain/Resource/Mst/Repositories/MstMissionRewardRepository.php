<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstMissionRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstMissionReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstMissionRewardRepository
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

    public function getById(string $id, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($id) {
            return $entity->getId() === $id;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_mission_rewards record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    /**
     * @return Collection<Entity>
     */
    public function getByGroupId(string $groupId): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupId) {
            return $entity->getGroupId() === $groupId;
        });
    }

    /**
     * @return Collection<Entity>
     */
    public function getByGroupIds(Collection $groupIds): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($groupIds) {
            return $groupIds->contains($entity->getGroupId());
        });
    }
}
