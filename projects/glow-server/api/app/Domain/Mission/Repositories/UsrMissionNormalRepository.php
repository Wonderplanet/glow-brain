<?php

declare(strict_types=1);

namespace App\Domain\Mission\Repositories;

use App\Domain\Mission\Entities\UsrMissionNormalBundle;
use App\Domain\Mission\Enums\MissionStatus;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Enums\MissionUnlockStatus;
use App\Domain\Mission\Models\Eloquent\UsrMissionNormal as EloquentUsrMissionNormal;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionNormal;
use App\Domain\Mission\Models\UsrMissionNormalInterface;
use App\Domain\Resource\Usr\Repositories\UsrModelMultiCacheRawRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UsrMissionNormalRepository extends UsrModelMultiCacheRawRepository
{
    protected string $modelClass = UsrMissionNormal::class;

    /**
     * クエリ実行回数の効率化のための、DB一括更新ロジックを記述するメソッド。
     * UsrModelManagerから呼び出される想定のため、実装者が直接実行することは想定していないです。
     */
    protected function saveModels(Collection $models): void
    {
        $upsertValues = $models->map(function (UsrMissionNormal $model) {
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

        EloquentUsrMissionNormal::query()->upsert(
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
    ): UsrMissionNormalInterface|IUsrMission {
        $model = UsrMissionNormal::create(
            usrUserId: $usrUserId,
            missionType: $missionType,
            mstMissionId: $mstMissionId,
            now: $now,
        );

        $this->syncModel($model);

        return $model;
    }

    /**
     * @param array<string> $mstMissionAchievementIds
     * @param array<string> $mstMissionBeginnerIds
     * @param array<string> $mstMissionDailyIds
     * @param array<string> $mstMissionWeeklyIds
     */
    public function getByMstMissionIds(
        string $usrUserId,
        array $mstMissionAchievementIds = [],
        array $mstMissionBeginnerIds = [],
        array $mstMissionDailyIds = [],
        array $mstMissionWeeklyIds = [],
    ): UsrMissionNormalBundle {
        $mstMissionAchievementIds = array_fill_keys($mstMissionAchievementIds, true);
        $mstMissionBeginnerIds = array_fill_keys($mstMissionBeginnerIds, true);
        $mstMissionDailyIds = array_fill_keys($mstMissionDailyIds, true);
        $mstMissionWeeklyIds = array_fill_keys($mstMissionWeeklyIds, true);

        $expectedCount = count($mstMissionAchievementIds) + count($mstMissionBeginnerIds)
            + count($mstMissionDailyIds) + count($mstMissionWeeklyIds);

        if ($expectedCount === 0) {
            return $this->makeUsrMissionNormalBundle(collect());
        }

        // dbCallback PK全指定でクエリを実行する
        $query = UsrMissionNormal::query()->where('usr_user_id', $usrUserId);
        $query->where(function ($query) use (
            $mstMissionAchievementIds,
            $mstMissionBeginnerIds,
            $mstMissionDailyIds,
            $mstMissionWeeklyIds,
        ) {
            if (count($mstMissionAchievementIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionAchievementIds) {
                    $query->where('mission_type', MissionType::ACHIEVEMENT->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionAchievementIds));
                });
            }
            if (count($mstMissionBeginnerIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionBeginnerIds) {
                    $query->where('mission_type', MissionType::BEGINNER->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionBeginnerIds));
                });
            }
            if (count($mstMissionDailyIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionDailyIds) {
                    $query->where('mission_type', MissionType::DAILY->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionDailyIds));
                });
            }
            if (count($mstMissionWeeklyIds) > 0) {
                $query->orWhere(function ($query) use ($mstMissionWeeklyIds) {
                    $query->where('mission_type', MissionType::WEEKLY->getIntValue())
                        ->whereIn('mst_mission_id', array_keys($mstMissionWeeklyIds));
                });
            }
        });

        $models = $this->cachedGetMany(
            $usrUserId,
            cacheCallback: function (Collection $cache) use (
                $mstMissionAchievementIds,
                $mstMissionBeginnerIds,
                $mstMissionDailyIds,
                $mstMissionWeeklyIds,
            ) {
                return $cache->filter(function (UsrMissionNormalInterface $model) use (
                    $mstMissionAchievementIds,
                    $mstMissionBeginnerIds,
                    $mstMissionDailyIds,
                    $mstMissionWeeklyIds,
                ) {
                    return match ($model->getMissionType()) {
                        MissionType::ACHIEVEMENT->getIntValue() =>
                            isset($mstMissionAchievementIds[$model->getMstMissionId()]),
                        MissionType::BEGINNER->getIntValue() =>
                            isset($mstMissionBeginnerIds[$model->getMstMissionId()]),
                        MissionType::DAILY->getIntValue() => isset($mstMissionDailyIds[$model->getMstMissionId()]),
                        MissionType::WEEKLY->getIntValue() => isset($mstMissionWeeklyIds[$model->getMstMissionId()]),
                        default => false,
                    };
                });
            },
            expectedCount: $expectedCount,
            dbCallback: function () use ($query) {
                return $query->get()
                    ->map(function ($record) {
                        return UsrMissionNormal::createFromRecord($record);
                    });
            },
        );

        return $this->makeUsrMissionNormalBundle($models);
    }

    /**
     * ミッションタイプごとにデータを整理したUsrMissionNormalBundleを生成する
     *
     * @param Collection<UsrMissionNormalInterface> $models
     */
    public function makeUsrMissionNormalBundle(Collection $models): UsrMissionNormalBundle
    {
        $achievements = [];
        $beginners = [];
        $dailies = [];
        $weeklies = [];

        foreach ($models as $model) {
            /** @var UsrMissionNormalInterface $model */
            $missionType = $model->getMissionType();
            switch ($missionType) {
                case MissionType::ACHIEVEMENT->getIntValue():
                    $achievements[$model->getMstMissionId()] = $model;
                    break;
                case MissionType::BEGINNER->getIntValue():
                    $beginners[$model->getMstMissionId()] = $model;
                    break;
                case MissionType::DAILY->getIntValue():
                    $dailies[$model->getMstMissionId()] = $model;
                    break;
                case MissionType::WEEKLY->getIntValue():
                    $weeklies[$model->getMstMissionId()] = $model;
                    break;
            }
        }

        return new UsrMissionNormalBundle(
            achievements: collect($achievements),
            beginners: collect($beginners),
            dailies: collect($dailies),
            weeklies:collect($weeklies),
        );
    }

    /**
     * 変更されたモデルのUsrMissionNormalBundleを生成する
     * @return UsrMissionNormalBundle
     */
    public function getUsrMissionNormalBundleOfChangedModels(): UsrMissionNormalBundle
    {
        return $this->makeUsrMissionNormalBundle($this->getChangedModels());
    }

    /**
     * 初心者ミッションの指定したミッションIDの報酬受取済数をキャッシュせずに取得する
     */
    public function getBeginnerReceivedRewardCountByMstMissionIds(
        string $usrUserId,
        Collection $mstMissionIds,
    ): int {
        if ($mstMissionIds->isEmpty()) {
            return 0;
        }

        $targetMstMissionIds = array_fill_keys($mstMissionIds->all(), true);

        $models = $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) use ($targetMstMissionIds) {
                return $cache->filter(function (UsrMissionNormalInterface $model) use ($targetMstMissionIds) {
                    return $model->getMissionType() === MissionType::BEGINNER->getIntValue()
                        && isset($targetMstMissionIds[$model->getMstMissionId()])
                        && $model->getStatus() === MissionStatus::RECEIVED_REWARD->value
                        && $model->isOpen();
                });
            },
            expectedCount: count($targetMstMissionIds),
            dbCallback: function () use ($usrUserId, $targetMstMissionIds) {
                return UsrMissionNormal::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('mission_type', MissionType::BEGINNER->getIntValue())
                    ->whereIn('mst_mission_id', array_keys($targetMstMissionIds))
                    ->where('status', MissionStatus::RECEIVED_REWARD->value)
                    ->where('is_open', MissionUnlockStatus::OPEN->value)
                    ->get()
                    ->map(function ($record) {
                        return UsrMissionNormal::createFromRecord($record);
                    });
            },
        );

        return $models->count();
    }

    /**
     * 開放済み かつ 達成済み のミッションデータを取得する
     */
    public function getReceivableRewards(
        string $usrUserId,
    ): UsrMissionNormalBundle {
        $models = $this->cachedGetMany(
            usrUserId: $usrUserId,
            cacheCallback: function (Collection $cache) {
                return $cache->filter(function (UsrMissionNormalInterface $model) {
                    return $model->getStatus() === MissionStatus::CLEAR->value
                        && $model->getIsOpen() === MissionUnlockStatus::OPEN->value;
                });
            },
            expectedCount: null,
            dbCallback: function () use ($usrUserId) {
                return UsrMissionNormal::query()
                    ->where('usr_user_id', $usrUserId)
                    ->where('status', MissionStatus::CLEAR->value)
                    ->where('is_open', MissionUnlockStatus::OPEN->value)
                    ->get()
                    ->map(function ($record) {
                        return UsrMissionNormal::createFromRecord($record);
                    });
            },
        );

        return $this->makeUsrMissionNormalBundle($models);
    }
}
