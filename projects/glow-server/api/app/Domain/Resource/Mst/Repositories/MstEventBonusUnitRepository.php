<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstEventBonusUnit as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstEventBonusUnitRepository
{
    public function __construct(
        private MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstEventBonusUnitEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param string $eventBonusGroupId
     * @param Collection $mstUnitIds
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstEventBonusUnitEntity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByEventBonusGroupIdAndMstUnitIds(string $eventBonusGroupId, Collection $mstUnitIds): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($eventBonusGroupId, $mstUnitIds) {
            /** @var \App\Domain\Resource\Mst\Entities\MstEventBonusUnitEntity $entity */
            return $entity->getEventBonusGroupId() === $eventBonusGroupId &&
                $mstUnitIds->contains($entity->getMstUnitId());
        });
    }
}
