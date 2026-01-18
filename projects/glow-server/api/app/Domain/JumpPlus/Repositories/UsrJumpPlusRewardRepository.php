<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Repositories;

use App\Domain\JumpPlus\Models\UsrJumpPlusReward;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrJumpPlusRewardRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = UsrJumpPlusReward::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrJumpPlusReward $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mng_jump_plus_reward_schedule_id' => $model->getMngJumpPlusRewardScheduleId(),
            ];
        })->toArray();

        UsrJumpPlusReward::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mng_jump_plus_reward_schedule_id'],
        );
    }

    /**
     * @return \Illuminate\Support\Collection<\App\Domain\JumpPlus\Models\UsrJumpPlusReward>
     */
    public function getByMngJumpPlusRewardScheduleIds(
        string $usrUserId,
        Collection $mngJumpPlusRewardScheduleIds
    ): Collection {
        if ($mngJumpPlusRewardScheduleIds->isEmpty()) {
            return collect();
        }

        $modelKeys = $mngJumpPlusRewardScheduleIds
            ->unique()
            ->map(function (string $mngJumpPlusRewardScheduleId) use ($usrUserId) {
                return UsrJumpPlusReward::makeModelKeyAsStatic($usrUserId, $mngJumpPlusRewardScheduleId);
            });

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($modelKeys) {
                return $cache->only($modelKeys->all());
            },
            expectedCount: $mngJumpPlusRewardScheduleIds->count(),
            dbCallback: function () use ($usrUserId, $mngJumpPlusRewardScheduleIds) {
                return UsrJumpPlusReward::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mng_jump_plus_reward_schedule_id', $mngJumpPlusRewardScheduleIds)
                    ->get();
            },
        );
    }

    public function createByMngJumpPlusRewardScheduleIds(
        string $usrUserId,
        Collection $mngJumpPlusRewardScheduleIds
    ): void {
        $mngJumpPlusRewardScheduleIds = $mngJumpPlusRewardScheduleIds->unique();

        $models = collect();
        foreach ($mngJumpPlusRewardScheduleIds as $mngJumpPlusRewardScheduleId) {
            $models->push(
                (new UsrJumpPlusReward())->init($usrUserId, $mngJumpPlusRewardScheduleId)
            );
        }
        $this->syncModels($models);
    }
}
