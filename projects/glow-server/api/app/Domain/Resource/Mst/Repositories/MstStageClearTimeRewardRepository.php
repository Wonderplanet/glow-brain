<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStageClearTimeRewardEntity as Entity;
use App\Domain\Resource\Mst\Models\MstStageClearTimeReward as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstStageClearTimeRewardRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $mstStageId
     * @return Collection<Entity>
     */
    public function getByMstStageId(string $mstStageId): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($mstStageId) {
            return $entity->getMstStageId() === $mstStageId;
        });
    }
}
