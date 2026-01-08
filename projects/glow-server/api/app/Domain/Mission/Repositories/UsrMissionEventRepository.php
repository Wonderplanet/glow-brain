<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Entities\UsrMissionEventBundle;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionEvent as EloquentUsrMissionEvent;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionEvent;
use App\Domain\Mission\Models\UsrMissionEventInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMissionEventRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrMissionEvent::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMissionEvent $model) {
            return [
                'id' => $model->getId(),
                'usr_user_id' => $model->getUsrUserId(),
                'mission_type' => $model->getMissionType(),
                'mst_mission_id' => $model->getMstMissionId(),
                'status' => $model->getStatus(),
                'is_open' => $model->getIsOpen(),
                'progress' => $model->getProgress(),
                'unlock_progress' => $model->getUnlockProgress(),
                'latest_reset_at' => $model->getLatestResetAt(),
                'cleared_at' => $model->getClearedAt(),
                'received_reward_at' => $model->getReceivedRewardAt(),
            ];
        })->toArray();

        EloquentUsrMissionEvent::query()->upsert(
            $upsertValues,
            ['usr_user_id', 'mission_type', 'mst_mission_id'],
            [
                'status',
                'is_open',
                'progress',
                'unlock_progress',
                'latest_reset_at',
                'cleared_at',
                'received_reward_at',
            ],
        );
    }

    public function create(
        string $usrUserId,
        int $missionType,
        string $mstMissionId,
        CarbonImmutable $now,
    ): UsrMissionEventInterface|IUsrMission {
        $model = UsrMissionEvent::create(
            usrUserId: $usrUserId,
            missionType: $missionType,
            mstMissionId: $mstMissionId,
            now: $now,
        );

        $this->syncModel($model);

        return $model;
    }

    /**
     * ミッションID配列から指定ミッションタイプのユーザーミッションデータを取得する
     *
     * @param array<string> $mstMissionEventIds
     * @param array<string> $mstMissionEventDailyIds
     */
    public function getByMstMissionIds(
        string $usrUserId,
        array $mstMissionEventIds = [],
        array $mstMissionEventDailyIds = [],
    ): UsrMissionEventBundle {
        $mstMissionEventIds = array_fill_keys($mstMissionEventIds, true);
        $mstMissionEventDailyIds = array_fill_keys($mstMissionEventDailyIds, true);

        $expectedCount = count($mstMissionEventIds) + count($mstMissionEventDailyIds);

        if ($expectedCount === 0) {
            return $this->makeUsrMissionEventBundle(collect());
        }

        // dbCallback PK全指定でクエリを実行する
        $query = UsrMissionEvent::query()->where('usr_user_id', $usrUserId);
        $query->where(function ($query) use (
            $mstMissionEventIds,
            $mstMissionEventDailyIds,
        ) {
            if (count($mstMissionEventIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionEventIds) {
                    $query->where('mission_type', MissionType::EVENT->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionEventIds));
                });
            }
            if (count($mstMissionEventDailyIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionEventDailyIds) {
                    $query->where('mission_type', MissionType::EVENT_DAILY->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionEventDailyIds));
                });
            }
        });

        $models = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use (
                $mstMissionEventIds,
                $mstMissionEventDailyIds,
            ) {
                return $cache->filter(function (UsrMissionEventInterface $model) use (
                    $mstMissionEventIds,
                    $mstMissionEventDailyIds,
                ) {
                    return match ($model->getMissionType()) {
                        MissionType::EVENT->getIntValue() =>
                            isset($mstMissionEventIds[$model->getMstMissionId()]),
                        MissionType::EVENT_DAILY->getIntValue() =>
                            isset($mstMissionEventDailyIds[$model->getMstMissionId()]),
                        default => false,
                    };
                });
            },
            expectedCount: $expectedCount,
            dbCallback: function () use ($query) {
                return $query->get()
                    ->map(function ($record) {
                        return UsrMissionEvent::createFromRecord($record);
                    });
            },
        );

        return $this->makeUsrMissionEventBundle($models);
    }

    /**
     * @param Collection<UsrMissionEventInterface> $models
     */
    public function makeUsrMissionEventBundle(Collection $models): UsrMissionEventBundle
    {
        $events = [];
        $eventDailies = [];

        foreach ($models as $model) {
            /** @var UsrMissionEventInterface $model */
            $missionType = $model->getMissionType();
            switch ($missionType) {
                case MissionType::EVENT->getIntValue():
                    $events[$model->getMstMissionId()] = $model;
                    break;
                case MissionType::EVENT_DAILY->getIntValue():
                    $eventDailies[$model->getMstMissionId()] = $model;
                    break;
            }
        }

        return new UsrMissionEventBundle(
            events: collect($events),
            eventDailies: collect($eventDailies),
        );
    }

    public function getUsrMissionEventBundleOfChangedModels(): UsrMissionEventBundle
    {
        return $this->makeUsrMissionEventBundle($this->getChangedModels());
    }

    /**
     * 開放済み かつ 達成済み のミッションデータを取得する
     */
    public function getReceivableRewards(
        string $usrUserId,
    ): UsrMissionEventBundle {
        $models = $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) {
                return $cache->filter(function (UsrMissionEventInterface $model) {
                    return $model->getStatus() === MissionStatus::CLEAR->value
                        && $model->getIsOpen() === MissionUnlockStatus::OPEN->value;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId) {
                return UsrMissionEvent::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('status', MissionStatus::CLEAR->value)
                    ->where('is_open', MissionUnlockStatus::OPEN->value)
                    ->get()
                    ->map(function ($record) {
                        return UsrMissionEvent::createFromRecord($record);
                    });
            },
        );

        return $this->makeUsrMissionEventBundle($models);
    }
}
