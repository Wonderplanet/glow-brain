<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStageRewardEntity;
use App\Domain\Resource\Mst\Models\MstStageReward;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstStageRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    private function getByMstStageId(string $mstStageId): Collection
    {
        return $this->masterRepository->getByColumn(MstStageReward::class, 'mst_stage_id', $mstStageId);
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getFirstClearRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getByMstStageId($mstStageId)->filter(
            fn(MstStageRewardEntity $entity) =>
            $entity->isFirstClear()
        );
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getAlwaysRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getByMstStageId($mstStageId)->filter(
            fn(MstStageRewardEntity $entity) =>
            $entity->isAlways()
        );
    }

    /**
     * @param string $mstStageId
     * @return Collection<\App\Domain\Resource\Mst\Entities\Contracts\IMstStageRewardEntity>
     */
    public function getRandomRewardsByMstStageId(string $mstStageId): Collection
    {
        return $this->getByMstStageId($mstStageId)->filter(
            fn(MstStageRewardEntity $entity) =>
                $entity->getMstStageId() === $mstStageId
                && $entity->isRandom()
        );
    }
}
