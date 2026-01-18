<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Repositories;

use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward;
use App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaRewardInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository as BaseRepository;
use Illuminate\Support\Collection;

class UsrReceivedUnitEncyclopediaRewardRepository extends BaseRepository
{
    protected string $modelClass = UsrReceivedUnitEncyclopediaReward::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrReceivedUnitEncyclopediaReward $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_unit_encyclopedia_reward_id' => $model->getMstUnitEncyclopediaRewardId(),
            ];
        })->toArray();

        UsrReceivedUnitEncyclopediaReward::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_unit_encyclopedia_reward_id'],
        );
    }

    public function create(
        string $usrUserId,
        string $mstUnitEncyclopediaRewardId
    ): UsrReceivedUnitEncyclopediaRewardInterface {
        $model = new UsrReceivedUnitEncyclopediaReward();

        $model->usr_user_id = $usrUserId;
        $model->mst_unit_encyclopedia_reward_id = $mstUnitEncyclopediaRewardId;

        $this->syncModel($model);

        return $model;
    }

    public function getList(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    public function getByMstUnitEncyclopediaRewardIds(string $usrUserId, Collection $rewardIds): Collection
    {
        if ($rewardIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($rewardIds) {
                return $cache->filter(function (UsrReceivedUnitEncyclopediaRewardInterface $model) use ($rewardIds) {
                    return $rewardIds->contains($model->getMstUnitEncyclopediaRewardId());
                });
            },
            expectedCount: $rewardIds->count(),
            dbCallback: function () use ($usrUserId, $rewardIds) {
                return UsrReceivedUnitEncyclopediaReward::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_unit_encyclopedia_reward_id', $rewardIds)
                    ->get();
            },
        )->keyBy(function (UsrReceivedUnitEncyclopediaRewardInterface $model) {
            return $model->getMstUnitEncyclopediaRewardId();
        });
    }
}
