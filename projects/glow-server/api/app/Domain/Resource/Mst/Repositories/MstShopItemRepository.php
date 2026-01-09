<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstShopItemEntity as Entity;
use App\Domain\Resource\Mst\Models\MstShopItem as Model;
use App\Infrastructure\MasterRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MstShopItemRepository
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
     * @param CarbonImmutable $now
     * @return Collection<Entity>
     */
    public function getActiveShopItems(CarbonImmutable $now): Collection
    {
        return $this->getAll()->filter(function ($entity) use ($now) {
            $startDate = new CarbonImmutable($entity->getStartDate());
            $endDate = new CarbonImmutable($entity->getEndDate());
            return $now->between($startDate, $endDate);
        });
    }

    public function getActiveShopItemById(
        string $mstShopItemId,
        CarbonImmutable $now,
        bool $isThrowError = false,
    ): ?Entity {
        $entity = $this->getActiveShopItems($now)
            ->filter(fn($product) => $product->getId() === $mstShopItemId)
            ->first();

        if ($isThrowError && is_null($entity)) {
            // 該当する商品データなし
            throw new GameException(
                ErrorCode::MST_NOT_FOUND,
                "mst_shop_items record is not found. (mst_shop_item_id: $mstShopItemId)",
            );
        }

        return $entity;
    }
}
