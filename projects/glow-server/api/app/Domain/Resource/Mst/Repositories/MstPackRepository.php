<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstPackEntity as Entity;
use App\Domain\Resource\Mst\Models\MstPack as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstPackRepository
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
                    'mst_packs record is not found. (id: %s)',
                    $id
                ),
            );
        }

        return $entities->first();
    }

    public function getByProductSubId(string $productSubId, bool $isThrowError = false): ?Entity
    {
        $entities = $this->getAll()->filter(function ($entity) use ($productSubId) {
            return $entity->getProductSubId() === $productSubId;
        });

        if ($isThrowError && $entities->isEmpty()) {
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                sprintf(
                    'mst_packs record is not found. (product_sub_id: %s)',
                    $productSubId
                ),
            );
        }

        return $entities->first();
    }

    public function getBySaleCondition(string $saleCondition): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($saleCondition) {
            return $entity->getSaleCondition() === $saleCondition;
        });
    }

    public function getSaleConditionPacks(): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) {
            return !is_null($entity->getSaleCondition());
        });
    }
}
