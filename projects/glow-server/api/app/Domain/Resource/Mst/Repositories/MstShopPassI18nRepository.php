<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstShopPassI18nEntity as Entity;
use App\Domain\Resource\Mst\Models\MstShopPassI18n as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstShopPassI18nRepository
{
    public function __construct(
        private readonly MasterRepository $masterRepository,
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
     * @param string $mstShopPassId
     * @return Entity|null
     */
    public function getMstShopPassId(string $mstShopPassId): ?Entity
    {
        return $this->getAll()
            ->filter(fn($entity) => $entity->getMstShopPassId() === $mstShopPassId)
            ->first();
    }

    public function getMstShopPassIds(Collection $mstShopPassIds): Collection
    {
        $targetMstShopPassIds = $mstShopPassIds->unique();

        $response = [];
        foreach ($targetMstShopPassIds as $targetMstShopPassId) {
            $mst = $this->getMstShopPassId($targetMstShopPassId);

            $response[$targetMstShopPassId] = $mst;
        }

        return collect($response);
    }
}
