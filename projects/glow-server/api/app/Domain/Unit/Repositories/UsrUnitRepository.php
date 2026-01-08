<?php

declare(strict_types=1);

namespace App\Domain\Unit\Repositories;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use App\Domain\Unit\Models\Eloquent\UsrUnit as EloquentUsrUnit;
use App\Domain\Unit\Models\UsrUnit;
use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UsrUnitRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrUnit::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrUnitInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_unit_id' => $model->getMstUnitId(),
                'level' => $model->getLevel(),
                'rank' => $model->getRank(),
                'grade_level' => $model->getGradeLevel(),
                'battle_count' => $model->getBattleCount(),
                'is_new_encyclopedia' => $model->getIsNewEncyclopedia(),
            ];
        })->toArray();

        EloquentUsrUnit::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_unit_id'],
            ['level', 'rank', 'grade_level', 'battle_count', 'is_new_encyclopedia'],
        );
    }

    public function getById(string $id, string $usrUserId): UsrUnitInterface
    {
        $model = $this->cachedGetOneWhere(
            $usrUserId,
            'id',
            $id,
            function () use ($id, $usrUserId) {
                $record = UsrUnit::query()
                    ->where('id', $id)
                    ->first();

                if ($record === null) {
                    return null;
                }

                $model = UsrUnit::createFromRecord($record);

                // 想定しない他人のデータなら、データがなかったとみなす
                if ($model->getUsrUserId() !== $usrUserId) {
                    return null;
                }

                return $model;
            },
        );

        if ($model === null) {
            throw new GameException(
                ErrorCode::UNIT_NOT_FOUND,
                "usr_unit record is not found. (usr_user_id: $usrUserId, usr_unit_id: $id)"
            );
        }

        return $model;
    }

    public function getByMstUnitId(string $usrUserId, string $mstUnitId): ?UsrUnitInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_unit_id',
            $mstUnitId,
            function () use ($usrUserId, $mstUnitId) {
                $record = UsrUnit::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_unit_id', $mstUnitId)
                    ->first();

                if ($record === null) {
                    return null;
                }

                return UsrUnit::createFromRecord($record);
            },
        );
    }

    /**
     * @return Collection<UsrUnitInterface>
     */
    public function getByIds(string $usrUserId, Collection $ids): Collection
    {
        $targetIds = array_fill_keys($ids->all(), true);

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetIds) {
                return $cache->filter(function (UsrUnitInterface $model) use ($targetIds) {
                    return isset($targetIds[$model->getId()]);
                });
            },
            expectedCount: count($targetIds),
            dbCallback: function () use ($usrUserId, $targetIds) {
                $models = UsrUnit::query()
                    ->whereIn('id', array_keys($targetIds))
                    ->get()
                    ->map(function ($record) {
                        return UsrUnit::createFromRecord($record);
                    });

                $targetModels = [];
                foreach ($models as $model) {
                    // 想定しない他人のデータは、データがなかったとみなす
                    if ($model->getUsrUserId() === $usrUserId) {
                        $targetModels[] = $model;
                    }
                }

                return collect($targetModels);
            },
        );
    }

    /**
     * @return Collection<string, UsrUnitInterface>
     * key: mst_unit_id, value: UsrUnitInterface
     */
    public function getByMstUnitIds(string $usrUserId, Collection $mstUnitIds): Collection
    {
        if ($mstUnitIds->isEmpty()) {
            return collect();
        }

        $targetMstUnitIds = array_fill_keys($mstUnitIds->all(), true);

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstUnitIds) {
                return $cache->filter(function (UsrUnitInterface $model) use ($targetMstUnitIds) {
                    return isset($targetMstUnitIds[$model->getMstUnitId()]);
                });
            },
            expectedCount: count($targetMstUnitIds),
            dbCallback: function () use ($usrUserId, $targetMstUnitIds) {
                return UsrUnit::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_unit_id', array_keys($targetMstUnitIds))
                    ->get()
                    ->map(function ($record) {
                        return UsrUnit::createFromRecord($record);
                    });
            },
        )->keyBy(function (UsrUnitInterface $model) {
            return $model->getMstUnitId();
        });
    }

    public function isCheckUnit(string $userId, string $mstUnitId): bool
    {
        $unit = $this->getByMstUnitId($userId, $mstUnitId);
        if (is_null($unit)) {
            return false;
        }
        return true;
    }

    /**
     * @api
     * @return Collection<UsrUnitInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
    {
        $usrUnit = UsrUnit::create(
            usrUserId: $usrUserId,
            mstUnitId: $mstUnitId,
        );

        $this->syncModel($usrUnit);

        return $usrUnit;
    }
}
