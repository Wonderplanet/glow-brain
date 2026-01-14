<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Repositories;

use App\Domain\Common\Utils\CacheKeyUtil;
use App\Domain\Resource\Mng\Entities\MngJumpPlusRewardBundle;
use App\Domain\Resource\Mng\Entities\MngJumpPlusRewardEntity;
use App\Domain\Resource\Mng\Entities\MngJumpPlusRewardScheduleEntity;
use App\Domain\Resource\Mng\Models\MngJumpPlusReward;
use App\Domain\Resource\Mng\Models\MngJumpPlusRewardSchedule;
use App\Infrastructure\MngCacheRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * mng_jump_plus_reward_schedules, mng_jump_plus_rewardsのデータを
 * スケジュールID1つごとにMngJumpPlusRewardBundleインスタンスとしてまとめて取得するためのRepository
 */
readonly class MngJumpPlusRewardBundleRepository
{
    public function __construct(
        private MngCacheRepository $mngCacheRepository,
    ) {
    }

    /**
     * @return Collection<string, MngJumpPlusRewardBundle>
     *   key: mng_jump_plus_reward_schedules.id, value: MngJumpPlusRewardBundle
     */
    private function getMngJumpPlusRewardBundles(CarbonImmutable $now): Collection
    {
        return $this->mngCacheRepository->getOrCreateCache(
            CacheKeyUtil::getMngJumpPlusRewardBundleKey(),
            fn() => $this->createMngJumpPlusRewardBundles($now),
        );
    }

    /**
     * 現在開催期間中のMngJumpPlusRewardデータ(MngJumpPlusRewardBundleとして1スケジュールをまとめている)を取得（キャッシュ対応）
     *
     * @param CarbonImmutable $now
     * @return Collection<string, MngJumpPlusRewardBundle>
     *   key: mng_jump_plus_reward_schedules.id, value: MngJumpPlusRewardBundle
     */
    public function getActiveMngJumpPlusRewardBundles(CarbonImmutable $now): Collection
    {
        $mngJumpPlusRewardBundles = $this->getMngJumpPlusRewardBundles($now);

        return $mngJumpPlusRewardBundles->filter(function (MngJumpPlusRewardBundle $bundle) use ($now) {
            return $bundle->isActive($now);
        });
    }

    /**
     * MngJumpPlusRewardBundleを作成
     * MngJumpPlusRewardBundle = 1スケジュールごとのデータをまとめたentity
     *
     * @return Collection<string, MngJumpPlusRewardBundle>
     *  key: mng_jump_plus_reward_schedules.id, value: MngJumpPlusRewardBundle
     */
    private function createMngJumpPlusRewardBundles(CarbonImmutable $now): Collection
    {
        $cacheBaseTime = $this->mngCacheRepository->getCacheBaseTime($now);

        // 期限切れしていないMngJumpPlusRewardScheduleデータを取得
        $mngJumpPlusRewardSchedules = $this->getActiveMngJumpPlusRewardSchedules($cacheBaseTime);

        if ($mngJumpPlusRewardSchedules->isEmpty()) {
            return collect();
        }

        $groupIds = $mngJumpPlusRewardSchedules
            ->map(function (MngJumpPlusRewardScheduleEntity $schedule) {
                return $schedule->getGroupId();
            })->unique()->values();

        // 報酬データを取得
        $mngJumpPlusRewards = $this->getMngJumpPlusRewardsByGroupIds($groupIds);

        // MngJumpPlusRewardBundleを作成
        $mngJumpPlusRewardBundles = collect();
        foreach ($mngJumpPlusRewardSchedules as $mngJumpPlusRewardSchedule) {
            $mngJumpPlusRewardBundles->put(
                $mngJumpPlusRewardSchedule->getId(),
                new MngJumpPlusRewardBundle(
                    $mngJumpPlusRewardSchedule,
                    $mngJumpPlusRewards->get(
                        $mngJumpPlusRewardSchedule->getGroupId(),
                        collect(),
                    ),
                ),
            );
        }

        return $mngJumpPlusRewardBundles;
    }

    /**
     * 期限切れしていないMngJumpPlusRewardScheduleデータを取得。
     * 期限切れしているデータはキャッシュに含めないようにするため。
     *
     * @return Collection<string, MngJumpPlusRewardScheduleEntity>
     *  key: mng_jump_plus_reward_schedules.id, value: MngJumpPlusRewardScheduleEntity
     */
    private function getActiveMngJumpPlusRewardSchedules(CarbonImmutable $now): Collection
    {
        $result = collect();
        $models = MngJumpPlusRewardSchedule::query()
            ->where('end_at', '>=', $now)
            ->get();
        foreach ($models as $model) {
            $entity = $model->toEntity();
            $result->put($entity->getId(), $entity);
        }

        return $result;
    }

    /**
     * 指定されたグループIDで報酬データを取得
     *
     * @param Collection<string> $groupIds mng_jump_plus_reward_schedules.group_id の配列
     * @return Collection<string, Collection<MngJumpPlusRewardEntity>>
     *  key: group_id, value: Collection<MngJumpPlusRewardEntity>
     */
    private function getMngJumpPlusRewardsByGroupIds(Collection $groupIds): Collection
    {
        $result = collect();
        $models = MngJumpPlusReward::query()
            ->whereIn('group_id', $groupIds->unique()->toArray())
            ->get();

        foreach ($models as $model) {
            $entity = $model->toEntity();
            $groupId = $entity->getGroupId();

            if (!$result->has($groupId)) {
                $result->put($groupId, collect());
            }

            $result->get($groupId)->push($entity);
        }

        return $result;
    }

    public function deleteAllCache(): void
    {
        $this->mngCacheRepository->deleteCache(CacheKeyUtil::getMngJumpPlusRewardBundleKey());
    }
}
