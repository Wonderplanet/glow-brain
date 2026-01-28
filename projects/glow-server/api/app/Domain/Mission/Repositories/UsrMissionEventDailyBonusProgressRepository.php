<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgress as EventBonusProgress;
use App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface as EventBonusProgressInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRepository;
use Illuminate\Support\Collection;

class UsrMissionEventDailyBonusProgressRepository extends UsrModelMultiCacheRepository
{
    protected string $modelClass = EventBonusProgress::class;

    // 1ユーザーあたりのレコード数が、最大でも1つの場合は、特別な処理を必要としない限り、saveModelsの記述は不要なため、削除して問題ないです。
    // 1ユーザーあたりのレコード数が、2つ以上想定される場合、saveModelsを記述してください。
    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (EventBonusProgress $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mst_mission_event_daily_bonus_schedule_id' => $model->getMstMissionEventDailyBonusScheduleId(),
                'progress' => $model->getProgress(),
                'latest_update_at' => $model->getLatestUpdateAt(),
            ];
        })->toArray();

        EventBonusProgress::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mst_mission_event_daily_bonus_schedule_id'],
            ['progress', 'latest_update_at'],
        );
    }

    public function create(string $usrUserId, string $mstMissionEventDailyBonusScheduleId): EventBonusProgressInterface
    {
        $model = new EventBonusProgress();

        $model->usr_user_id = $usrUserId;
        $model->mst_mission_event_daily_bonus_schedule_id = $mstMissionEventDailyBonusScheduleId;
        $model->progress = 0;
        $model->latest_update_at = null;

        $this->syncModel($model);

        return $model;
    }

    public function getByMstScheduleId(string $usrUserId, string $mstScheduleId): ?EventBonusProgressInterface
    {
        return $this->cachedGetOneWhere(
            usrUserId: $usrUserId,
            columnKey: 'mst_mission_event_daily_bonus_id',
            columnValue: $mstScheduleId,
            dbCallback: function () use ($usrUserId, $mstScheduleId) {
                return EventBonusProgress::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mst_mission_event_daily_bonus_schedule_id', $mstScheduleId)
                    ->first();
            },
        );
    }

    /**
     * @return Collection<EventBonusProgressInterface>
     */
    public function getByMstScheduleIds(string $usrUserId, Collection $mstScheduleIds): Collection
    {
        if ($mstScheduleIds->isEmpty()) {
            return collect();
        }

        return $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($mstScheduleIds) {
                return $cache->filter(function (EventBonusProgressInterface $model) use ($mstScheduleIds) {
                    return $mstScheduleIds->contains($model->getMstMissionEventDailyBonusScheduleId());
                });
            },
            expectedCount: $mstScheduleIds->count(),
            dbCallback: function () use ($usrUserId, $mstScheduleIds) {
                return EventBonusProgress::query()
                    ->where('usr_user_id', $usrUserId)
                    ->whereIn('mst_mission_event_daily_bonus_schedule_id', $mstScheduleIds->toArray())
                    ->get();
            },
        );
    }
}
