<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStageEventRewardEntity;
use App\Domain\Resource\Mst\Models\MstStageEventReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstStageEventRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    private function getAll(): Collection
    {
        return $this->masterRepository->get(MstStageEventReward::class);
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getFirstClearRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getAll()->filter(
            fn(MstStageEventRewardEntity $entity) =>
            $entity->getMstStageId() === $mstStageId
            && $entity->isFirstClear()
        );
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getAlwaysRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getAll()->filter(
            fn(MstStageEventRewardEntity $entity) =>
            $entity->getMstStageId() === $mstStageId
            && $entity->isAlways()
        );
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getRandomRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getAll()->filter(
            fn(MstStageEventRewardEntity $entity) =>
                $entity->getMstStageId() === $mstStageId
                && $entity->isRandom()
        );
    }
}
