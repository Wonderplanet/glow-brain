<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Models\MstShopPassEffect as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstShopPassEffectRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
    ) {
    }

    /**
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassEffectEntity>
     */
    private function getAll(): Collection
    {
        return $this->masterRepository->get(Model::class);
    }

    /**
     * @param array<int, string> $mstShopPassIds
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassEffectEntity>
     */
    public function getListByMstShopPassIds(array $mstShopPassIds): Collection
    {
        return $this->getAll()
            ->filter(
                function ($entity) use ($mstShopPassIds) {
                    return in_array($entity->getMstShopPassId(), $mstShopPassIds, true);
                }
            );
    }

    /**
     * @param string $mstShopPassId
     * @property string $effect_type
     * @return Collection<\App\Domain\Resource\Mst\Entities\MstShopPassEffectEntity>
     */
    public function getMstShopPassIdAndEffectType(string $mstShopPassId, string $effectType): Collection
    {
        return $this->getAll()
            ->filter(
                function ($entity) use ($mstShopPassId, $effectType) {
                    return $entity->getMstShopPassId() === $mstShopPassId
                        && $entity->getEffectType() === $effectType;
                }
            );
    }
}
