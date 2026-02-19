<?php

declare(strict_types=1);

namespace App\Domain\Exchange\Repositories;

use App\Domain\Exchange\Models\UsrExchangeLineup;
use App\Domain\Exchange\Models\UsrExchangeLineupInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrExchangeLineupRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrExchangeLineup::class;

    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrExchangeLineup $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_exchange_lineup_id' => $model->getMstExchangeLineupId(),
                'mst_exchange_id' => $model->getMstExchangeId(),
                'trade_count' => $model->getTradeCount(),
                'reset_at' => $model->getResetAt(),
            ];
        })->toArray();

        UsrExchangeLineup::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_exchange_lineup_id', 'mst_exchange_id'],
            ['trade_count', 'reset_at'],
        );
    }

    /**
     * ユーザーID、ラインナップID、交換所IDで取得
     */
    public function get(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId
    ): ?UsrExchangeLineupInterface {
        $results = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstExchangeLineupId, $mstExchangeId) {
                return $cache->filter(
                    function (UsrExchangeLineupInterface $model) use ($mstExchangeLineupId, $mstExchangeId) {
                        return $model->getMstExchangeLineupId() === $mstExchangeLineupId
                            && $model->getMstExchangeId() === $mstExchangeId;
                    }
                );
            },
            expectedCount: 1,
            dbCallback: function () use ($usrUserId, $mstExchangeLineupId, $mstExchangeId) {
                return UsrExchangeLineup::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_exchange_lineup_id', $mstExchangeLineupId)
                    ->where('mst_exchange_id', $mstExchangeId)
                    ->get();
            }
        );

        return $results->first();
    }

    /**
     * 新規作成
     */
    private function create(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId,
        CarbonImmutable $now
    ): UsrExchangeLineupInterface {
        $model = new UsrExchangeLineup();
        $model->usr_user_id = $usrUserId;
        $model->mst_exchange_lineup_id = $mstExchangeLineupId;
        $model->mst_exchange_id = $mstExchangeId;
        $model->trade_count = 0;
        $model->reset_at = $now->toDateTimeString();

        $this->syncModel($model);

        return $model;
    }

    /**
     * ユーザーID、ラインナップID、交換所IDで取得（なければ新規作成）
     */
    public function getOrCreate(
        string $usrUserId,
        string $mstExchangeLineupId,
        string $mstExchangeId,
        CarbonImmutable $now
    ): UsrExchangeLineupInterface {
        $model = $this->get($usrUserId, $mstExchangeLineupId, $mstExchangeId);
        if ($model === null) {
            $model = $this->create($usrUserId, $mstExchangeLineupId, $mstExchangeId, $now);
        }
        return $model;
    }

    /**
     * 指定した交換所IDに対応する交換履歴を取得
     *
     * @param Collection<string> $mstExchangeIds
     * @return Collection<UsrExchangeLineupInterface>
     */
    public function getListByMstExchangeIds(string $usrUserId, Collection $mstExchangeIds): Collection
    {
        return $this->cachedGetMany(
            $usrUserId,
            expectedCount: null,
            cacheCallback: function (Collection $cache) use ($mstExchangeIds) {
                return $cache->filter(
                    function (UsrExchangeLineupInterface $model) use ($mstExchangeIds) {
                        return $mstExchangeIds->contains($model->getMstExchangeId());
                    }
                );
            },
            dbCallback: function () use ($usrUserId, $mstExchangeIds) {
                return UsrExchangeLineup::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_exchange_id', $mstExchangeIds)
                    ->get();
            }
        );
    }
}
