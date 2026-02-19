<?php

declare(strict_types=1);

namespace App\Domain\Shop\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Shop\Models\UsrShopItem;
use App\Domain\Shop\Models\UsrShopItemInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrShopItemRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrShopItem::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrShopItemInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_shop_item_id' => $model->getMstShopItemId(),
                'trade_count' => $model->getTradeCount(),
                'trade_total_count' => $model->getTradeTotalCount(),
                'last_reset_at' => $model->getLastResetAt(),
            ];
        })->toArray();

        UsrShopItem::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_shop_item_id'],
            ['trade_count', 'trade_total_count', 'last_reset_at']
        );
    }

    public function create(string $usrUserId, string $mstShopItemId, CarbonImmutable $now): UsrShopItem
    {
        $usrShopItem = new UsrShopItem();
        $usrShopItem->usr_user_id = $usrUserId;
        $usrShopItem->mst_shop_item_id = $mstShopItemId;
        $usrShopItem->trade_count = 0;
        $usrShopItem->trade_total_count = 0;
        $usrShopItem->last_reset_at = $now->format('Y-m-d H:i:s');

        $this->syncModel($usrShopItem);

        return $usrShopItem;
    }

    public function get(string $usrUserId, string $mstShopItemId): ?UsrShopItem
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_shop_item_id',
            $mstShopItemId,
            function () use ($usrUserId, $mstShopItemId) {
                return UsrShopItem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_shop_item_id', $mstShopItemId)
                    ->first();
            }
        );
    }

    public function findOrCreate(string $usrUserId, string $mstShopItemId, CarbonImmutable $now): UsrShopItem
    {
        $usrShopItem = $this->get($usrUserId, $mstShopItemId);
        if ($usrShopItem === null) {
            $usrShopItem = $this->create($usrUserId, $mstShopItemId, $now);
        }
        return $usrShopItem;
    }

    /**
     * @return Collection<UsrShopItemInterface>
     */
    public function getList(string $userId): Collection
    {
        return $this->cachedGetAll($userId);
    }

    /**
     * @return Collection<UsrShopItemInterface>
     */
    public function getByMstShopItemIds(string $usrUserId, Collection $mstShopItemIds): Collection
    {
        if ($mstShopItemIds->isEmpty()) {
            return collect();
        }
        $mstShopItemIds = $mstShopItemIds->unique();

        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstShopItemIds) {
                return $cache->filter(function (UsrShopItemInterface $model) use ($mstShopItemIds) {
                    return $mstShopItemIds->contains($model->getMstShopItemId());
                });
            },
            expectedCount: $mstShopItemIds->count(),
            dbCallback: function () use ($usrUserId, $mstShopItemIds) {
                return UsrShopItem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_shop_item_id', $mstShopItemIds)
                    ->get();
            }
        );
    }
}
