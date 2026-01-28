<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use App\Domain\Stage\Models\Eloquent\UsrStage as EloquentUsrStage;
use App\Domain\Stage\Models\UsrStage;
use App\Domain\Stage\Models\UsrStageInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrStageRepository extends UsrModelMultiCacheRawRepository implements IUsrStageRepository
{
    protected string $modelClass = UsrStage::class;

    public function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrStageInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_stage_id' => $model->getMstStageId(),
                'clear_count' => $model->getClearCount(),
                'clear_time_ms' => $model->getClearTimeMs(),
            ];
        })->toArray();

        EloquentUsrStage::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_stage_id'],
            ['clear_count', 'clear_time_ms'],
        );
    }

    public function findByMstStageId(string $usrUserId, string $mstStageId): ?UsrStageInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_stage_id',
            $mstStageId,
            function () use ($usrUserId, $mstStageId) {
                $record = UsrStage::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_stage_id', $mstStageId)
                    ->first();
                if (is_null($record)) {
                    return null;
                }

                return UsrStage::createFromRecord($record);
            },
        );
    }

    public function findByMstStageIds(string $usrUserId, Collection $mstStageIds): Collection
    {
        if ($mstStageIds->isEmpty()) {
            return collect();
        }

        $targetMstStageIds = array_fill_keys($mstStageIds->all(), true);

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstStageIds) {
                return $cache->filter(function (UsrStageInterface $model) use ($targetMstStageIds) {
                    return isset($targetMstStageIds[$model->getMstStageId()]);
                });
            },
            expectedCount: count($targetMstStageIds),
            dbCallback: function () use ($usrUserId, $targetMstStageIds) {
                return UsrStage::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_stage_id', array_keys($targetMstStageIds))
                    ->get()
                    ->map(fn ($record) => UsrStage::createFromRecord($record));
            },
        )->keyBy(function (UsrStageInterface $model) {
            return $model->getMstStageId();
        });
    }

    /**
     * @api
     * @return Collection<UsrStageInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function create(string $usrUserId, string $mstStageId, ?CarbonImmutable $now = null): UsrStageInterface
    {
        $usrStage = UsrStage::create(
            usrUserId: $usrUserId,
            mstStageId: $mstStageId,
            now: $now,
        );

        $this->syncModel($usrStage);

        return $usrStage;
    }

    public function getOrCreateByMstStageId(string $usrUserId, string $mstStageId): UsrStageInterface
    {
        $model = $this->findByMstStageId($usrUserId, $mstStageId);
        if (is_null($model)) {
            $model = $this->create($usrUserId, $mstStageId);
        }

        return $model;
    }

    /**
     * クリア済みステージを全て取得
     */
    public function getClearList(string $usrUserId): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) {
                return $cache->filter(function (UsrStageInterface $model) {
                    return $model->isClear();
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId) {
                return UsrStage::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('clear_count', '>', 0)
                    ->get()
                    ->map(fn ($record) => UsrStage::createFromRecord($record));
            },
        );
    }

    /**
     * 指定したステージの内で、クリア済みステージを取得
     * @param Collection $mstStageIds
     * @return Collection<UsrStageInterface>
     */
    public function getClearListByMstStageIds(string $usrUserId, Collection $mstStageIds): Collection
    {
        if ($mstStageIds->isEmpty()) {
            return collect();
        }

        $targetMstStageIds = array_fill_keys($mstStageIds->all(), true);

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstStageIds) {
                return $cache->filter(function (UsrStageInterface $model) use ($targetMstStageIds) {
                    return $model->isClear() && isset($targetMstStageIds[$model->getMstStageId()]);
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId, $targetMstStageIds) {
                return UsrStage::query()
                ->where('usr_user_id', $usrUserId)
                ->whereIn('mst_stage_id', array_keys($targetMstStageIds))
                ->where('clear_count', '>', 0)
                ->get()
                ->map(fn ($record) => UsrStage::createFromRecord($record));
            },
        );
    }
}
