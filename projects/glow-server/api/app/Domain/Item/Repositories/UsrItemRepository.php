<?php

declare(strict_types=1);

namespace App\Domain\Item\Repositories;

use App\Domain\Item\Models\Eloquent\UsrItem as EloquentUsrItem;
use App\Domain\Item\Models\UsrItem;
use App\Domain\Item\Models\UsrItemInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Illuminate\Support\Collection;

class UsrItemRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrItem::class;

    /**
     * @param Collection<UsrItemInterface> $models
     * @return void
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrItemInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_item_id' => $model->getMstItemId(),
                'amount' => $model->getAmount(),
            ];
        })->toArray();

        EloquentUsrItem::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_item_id'],
            ['amount'],
        );
    }

    public function create(string $usrUserId, string $mstItemId, int $amount): UsrItemInterface
    {
        $model = UsrItem::create(
            usrUserId: $usrUserId,
            mstItemId: $mstItemId,
            amount: $amount,
        );

        $this->syncModel($model);

        return $model;
    }

    /**
     * @return Collection<UsrItemInterface>
     */
    public function getList(string $userId): Collection
    {

        return $this->cachedGetAll($userId);
    }

    /**
     * @return Collection<string, UsrItemInterface>
     */
    public function getListByMstItemIds(string $usrUserId, Collection $mstItemIds): Collection
    {
        $targetMstItemIds = array_fill_keys($mstItemIds->all(), true);

        $models = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstItemIds) {
                return $cache->filter(function (UsrItemInterface $model) use ($targetMstItemIds) {
                    return isset($targetMstItemIds[$model->getMstItemId()]);
                });
            },
            expectedCount: count($targetMstItemIds),
            dbCallback: function () use ($usrUserId, $targetMstItemIds) {
                return UsrItem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_item_id', array_keys($targetMstItemIds))
                    ->get()
                    ->map(function ($record) {
                        return UsrItem::createFromRecord($record);
                    });
            },
        );

        return $models->keyBy(function (UsrItemInterface $model) {
            return $model->getMstItemId();
        });
    }

    public function getByMstItemId(string $usrUserId, string $mstItemId): ?UsrItemInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_item_id',
            $mstItemId,
            function () use ($usrUserId, $mstItemId) {
                $record = UsrItem::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_item_id', $mstItemId)
                    ->first();

                if ($record === null) {
                    return null;
                }

                return UsrItem::createFromRecord($record);
            }
        );
    }
}
