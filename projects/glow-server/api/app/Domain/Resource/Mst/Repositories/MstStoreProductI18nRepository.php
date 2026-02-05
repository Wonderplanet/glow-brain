<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Resource\Mst\Entities\MstStoreProductI18nEntity as Entity;
use App\Domain\Resource\Mst\Models\MstStoreProductI18n as Model;
use App\Infrastructure\MasterRepository;
use Illuminate\Support\Collection;

class MstStoreProductI18nRepository
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
     * mst_store_product_idから価格情報を取得
     *
     * @param Collection<string> $mstStoreProductIds mst_store_product_idのCollection
     * @return Collection<Entity>
     */
    public function getByMstStoreProductIds(Collection $mstStoreProductIds): Collection
    {
        return $this->getAll()->filter(function (Entity $entity) use ($mstStoreProductIds) {
            return $mstStoreProductIds->contains($entity->getMstStoreProductId());
        });
    }
}
