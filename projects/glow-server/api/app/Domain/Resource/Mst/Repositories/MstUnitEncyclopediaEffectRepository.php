<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstUnitEncyclopediaEffectEntity as Entity;
use App\Domain\Resource\Mst\Models\MstUnitEncyclopediaEffect as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

readonly class MstUnitEncyclopediaEffectRepository
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
     * @param Collection<string> $mstUnitEncyclopediaRewardIds
     * @return Collection<Entity>
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function getByMstUnitEncyclopediaRewardIds(Collection $mstUnitEncyclopediaRewardIds): Collection
    {
        $entities = $this->getAll()->filter(function (Entity $entity) use ($mstUnitEncyclopediaRewardIds) {
            return $mstUnitEncyclopediaRewardIds->containsStrict($entity->getMstUnitEncyclopediaRewardId());
        });
        return $entities->values();
    }

    /**
     * @return Collection<string, Entity>
     */
    public function getByIds(Collection $ids): Collection
    {
        return $this->getAll()->only($ids->unique()->toArray());
    }
}
