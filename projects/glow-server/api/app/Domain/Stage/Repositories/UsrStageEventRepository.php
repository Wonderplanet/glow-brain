<?php

declare(strict_types=1);

namespace App\Domain\Stage\Repositories;

use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use App\Domain\Stage\Models\UsrStageEvent;
use App\Domain\Stage\Models\UsrStageEventInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrStageEventRepository extends UsrModelMultiCacheRepository implements IUsrStageRepository
{
    protected string $modelClass = UsrStageEvent::class;

    public function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrStageEventInterface $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_stage_id' => $model->getMstStageId(),
                'clear_count' => $model->getClearCount(),
                'reset_clear_count' => $model->getResetClearCount(),
                'reset_ad_challenge_count' => $model->getResetAdChallengeCount(),
                'latest_reset_at' => $model->getLatestResetAt(),
                'last_challenged_at' => $model->getLastChallengedAt(),
                'latest_event_setting_end_at' => $model->getLatestEventSettingEndAt(),
                'reset_clear_time_ms' => $model->getResetClearTimeMs(),
                'clear_time_ms' => $model->getClearTimeMs(),
            ];
        })->toArray();

        UsrStageEvent::upsert(
            $upsertValues,
            ['usr_user_id', 'mst_stage_id'],
        );
    }

    public function findByMstStageId(string $usrUserId, string $mstStageId): ?UsrStageEventInterface
    {
        return $this->cachedGetOneWhere(
            $usrUserId,
            'mst_stage_id',
            $mstStageId,
            function () use ($usrUserId, $mstStageId) {
                return UsrStageEvent::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_stage_id', $mstStageId)
                    ->first();
            },
        );
    }

    /**
     * @return Collection<string, UsrStageEventInterface>
     */
    public function findByMstStageIds(string $usrUserId, Collection $mstStageIds): Collection
    {
        if ($mstStageIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstStageIds) {
                return $cache->filter(function (UsrStageEventInterface $model) use ($mstStageIds) {
                    return $mstStageIds->contains($model->getMstStageId());
                });
            },
            expectedCount: $mstStageIds->count(),
            dbCallback: function () use ($usrUserId, $mstStageIds) {
                return UsrStageEvent::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_stage_id', $mstStageIds)
                    ->get();
            },
        )->keyBy(function (UsrStageEventInterface $model) {
            return $model->getMstStageId();
        });
    }

    /**
     * @return Collection<UsrStageEventInterface>
     */
    public function getListByUsrUserId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId);
    }

    /**
     * @api
     * @return Collection<UsrStageEventInterface>
     */
    public function getListKeyByMstStageId(string $usrUserId): Collection
    {
        return $this->cachedGetAll($usrUserId)->keyBy(function (UsrStageEventInterface $model) {
            return $model->getMstStageId();
        });
    }

    public function create(string $usrUserId, string $mstStageId, ?CarbonImmutable $now = null): UsrStageEventInterface
    {
        $usrStageEvent = new UsrStageEvent();
        $usrStageEvent->usr_user_id = $usrUserId;
        $usrStageEvent->mst_stage_id = $mstStageId;
        $usrStageEvent->clear_count = 0;
        $usrStageEvent->reset_clear_count = 0;
        $usrStageEvent->reset_ad_challenge_count = 0;
        $usrStageEvent->latest_reset_at = $now?->toDateTimeString();
        $usrStageEvent->last_challenged_at = null;
        $usrStageEvent->latest_event_setting_end_at = '2000-01-01 00:00:00';// デフォルト値を設定

        $this->syncModel($usrStageEvent);

        return $usrStageEvent;
    }
}
