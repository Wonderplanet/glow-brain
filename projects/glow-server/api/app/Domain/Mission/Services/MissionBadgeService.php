<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Models\UsrMissionEventInterface;
use App\Domain\Mission\Models\UsrMissionLimitedTermInterface;
use App\Domain\Mission\Models\UsrMissionNormalInterface;
use App\Domain\Mission\Repositories\UsrMissionEventRepository;
use App\Domain\Mission\Repositories\UsrMissionLimitedTermRepository;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Resource\Entities\MissionUnreceivedEventReward;
use App\Domain\Resource\Entities\MissionUnreceivedLimitedTermReward;
use App\Domain\Resource\Entities\MissionUnreceivedReward;
use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermEntity;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionLimitedTermRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionWeeklyRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionBadgeService
{
    public function __construct(
        // MstRepository
        private MstMissionAchievementRepository $mstMissionAchievementRepository,
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        private MstMissionDailyRepository $mstMissionDailyRepository,
        private MstMissionWeeklyRepository $mstMissionWeeklyRepository,
        private MstMissionEventDailyRepository $mstMissionEventDailyRepository,
        private MstMissionEventRepository $mstMissionEventRepository,
        private MstMissionLimitedTermRepository $mstMissionLimitedTermRepository,
        private MstEventRepository $mstEventRepository,
        // UsrRepository
        private UsrMissionEventRepository $usrMissionEventRepository,
        private UsrMissionLimitedTermRepository $usrMissionLimitedTermRepository,
        private UsrMissionNormalRepository $usrMissionNormalRepository,
        // Service
        private MissionUpdateService $missionUpdateService,
    ) {
    }

    /**
     * 各ミッションタイプごとに、未受取報酬数を取得し、合計値を返す
     * 対象ミッションタイプ：Achievement, Beginner, Daily, Weekly, DailyBonus
     */
    public function fetchUnreceivedRewardData(string $usrUserId, CarbonImmutable $now): MissionUnreceivedReward
    {
        // 開放済のみを取得
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getReceivableRewards($usrUserId);

        // achievement
        $mstMissionAchievements = $this->mstMissionAchievementRepository->getByIds(
            $usrMissionNormalBundle->getMstMissionAchievementIds()
        );
        // beginner
        $mstMissionBeginners = $this->mstMissionBeginnerRepository->getByIds(
            $usrMissionNormalBundle->getMstMissionBeginnerIds()
        );

        // daily
        /**
         * @var Collection<string, UsrMissionNormalInterface>
         *   key: mst_mission_id, value: UsrMissionNormalInterface
         */
        $usrMissionDailies = $this->missionUpdateService
            ->resetDailyUsrMissions($usrMissionNormalBundle->getDailies(), $now)
            ->filter(fn (UsrMissionNormalInterface $model) => $model->canReceiveReward());
        $mstMissionDailies = $this->mstMissionDailyRepository->getByIds($usrMissionDailies->keys());

        // weekly
        /**
         * @var Collection<string, UsrMissionNormalInterface>
         *   key: mst_mission_id, value: UsrMissionNormalInterface
         */
        $usrMissionWeeklies = $this->missionUpdateService
            ->resetWeeklyUsrMissions($usrMissionNormalBundle->getWeeklies(), $now)
            ->filter(fn (UsrMissionNormalInterface $model) => $model->canReceiveReward());
        $mstMissionWeeklies = $this->mstMissionWeeklyRepository->getByIds($usrMissionWeeklies->keys());

        // entityにまとめる
        $missionUnreceiveReward = new MissionUnreceivedReward(
            $mstMissionAchievements->count(),
            $mstMissionDailies->count(),
            $mstMissionWeeklies->count(),
            $mstMissionBeginners->count(),
        );

        return $this->deactivateBadgeIfNecessaryByMissionUnreceivedReward($usrUserId, $missionUnreceiveReward);
    }

    /**
     * 特定条件下でバッジを0にする。
     *
     * daily,weeklyミッションは、ボーナスポイントミッションのみで報酬を受け取れるため、
     * ボーナスポイントミッションを全達成していたら、バッジ通知する必要がない。
     */
    private function deactivateBadgeIfNecessaryByMissionUnreceivedReward(
        string $usrUserId,
        MissionUnreceivedReward $missionUnreceivedReward,
    ): MissionUnreceivedReward {
        if (
            $missionUnreceivedReward->getDailyCount() === 0
            && $missionUnreceivedReward->getWeeklyCount() === 0
        ) {
            // カウント調整対象のミッションタイプのバッジが0なので調整処理不要
            return $missionUnreceivedReward;
        }

        // 対象ミッションタイプのバッジの返却が必要かどうかを判定する
        $needDaily = true;
        $needWeekly = true;

        // ボーナスミッションは達成時に自動受取なので、最後のミッションで判定する
        $mstMissionDailyIds = [];
        $mstMissionWeeklyIds = [];

        if ($missionUnreceivedReward->getDailyCount() > 0) {
            $mstMissionDaily = $this->mstMissionDailyRepository->getLastBonusPointMission();
            if ($mstMissionDaily !== null) {
                $mstMissionDailyIds[] = $mstMissionDaily->getId();
            }
        }

        if ($missionUnreceivedReward->getWeeklyCount() > 0) {
            $mstMissionWeekly = $this->mstMissionWeeklyRepository->getLastBonusPointMission();
            if ($mstMissionWeekly !== null) {
                $mstMissionWeeklyIds[] = $mstMissionWeekly->getId();
            }
        }

        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(
            $usrUserId,
            [],
            [],
            $mstMissionDailyIds,
            $mstMissionWeeklyIds,
        );

        $dailyUsrMissionNormal = $usrMissionNormalBundle->getDailies()->first();
        if ($dailyUsrMissionNormal !== null) {
            $needDaily = !$dailyUsrMissionNormal->isReceivedReward();
        }

        $weeklyUsrMissionNormal = $usrMissionNormalBundle->getWeeklies()->first();
        if ($weeklyUsrMissionNormal !== null) {
            $needWeekly = !$weeklyUsrMissionNormal->isReceivedReward();
        }

        return new MissionUnreceivedReward(
            $missionUnreceivedReward->getAchievementCount(),
            $needDaily ? $missionUnreceivedReward->getDailyCount() : 0,
            $needWeekly ? $missionUnreceivedReward->getWeeklyCount() : 0,
            $missionUnreceivedReward->getBeginnerCount(),
        );
    }

    /**
     * 各ミッションタイプごとに、未受取報酬数を取得し、合計値を返す
     * 対象ミッションタイプ：Event, EventDaily
     */
    public function fetchUnreceivedEventRewardCount(
        string $usrUserId,
        CarbonImmutable $now
    ): MissionUnreceivedEventReward {
        $mstEventIds = $this->mstEventRepository->getAllActiveEvents($now)->keys();

        $usrMissionEventBundle = $this->usrMissionEventRepository->getReceivableRewards($usrUserId);

        // event
        $mstMissionEvents = $this->mstMissionEventRepository->getByIdsAndMstEventIds(
            $usrMissionEventBundle->getEvents()->keys(),
            $mstEventIds
        );
        $eventUsrMissions = $this->missionUpdateService
            ->resetEventUsrMissions($usrMissionEventBundle->getEvents(), $mstMissionEvents, $now)
            ->filter(fn (UsrMissionEventInterface $model) => $model->canReceiveReward());
        $mstMissionEvents = $mstMissionEvents->only($eventUsrMissions->keys());

        // event_daily
        $eventDailyUsrMissions = $this->missionUpdateService
            ->resetEventDailyUsrMissions($usrMissionEventBundle->getEventDailies(), $now)
            ->filter(fn (UsrMissionEventInterface $model) => $model->canReceiveReward());
        $mstMissionEventDailies = $this->mstMissionEventDailyRepository->getByIdsAndMstEventIds(
            $eventDailyUsrMissions->keys(),
            $mstEventIds
        );

        // mst_event_id ごとに集計
        $eventCountMap = $mstMissionEvents->countBy(fn ($entity) => $entity->getMstEventId());
        $eventDailyCountMap = $mstMissionEventDailies->countBy(fn ($entity) => $entity->getMstEventId());

        return new MissionUnreceivedEventReward(
            $eventCountMap,
            $eventDailyCountMap,
        );
    }

    /**
     * 降臨バトルの未受取報酬数を取得し、合計値を返す
     */
    public function fetchUnreceivedLimitedTermRewardCount(
        string $usrUserId,
        CarbonImmutable $now
    ): MissionUnreceivedLimitedTermReward {
        $usrMissionLimitedTerms = $this->usrMissionLimitedTermRepository->getReceivableRewards($usrUserId)
            ->keyBy(fn (UsrMissionLimitedTermInterface $model) => $model->getMstMissionId());

        $mstMissionLimitedTerms = $this->mstMissionLimitedTermRepository->getActivesByIds(
            $usrMissionLimitedTerms->keys(),
            $now,
        );

        $usrMissionLimitedTerms = $this->missionUpdateService->resetLimitedTermUsrMissions(
            $usrMissionLimitedTerms,
            $mstMissionLimitedTerms,
            $now,
        )->filter(fn (UsrMissionLimitedTermInterface $model) => $model->canReceiveReward());

        $filteredMstMissionLimitedTerms = $mstMissionLimitedTerms->only($usrMissionLimitedTerms->keys());

        $groupedByCategory = $filteredMstMissionLimitedTerms
            ->groupBy(fn (MstMissionLimitedTermEntity $entity) => $entity->getMissionCategory());

        // 原画パネルミッションはprogress_group_key（= mst_artwork_panel_missions.id）でグループ化
        $artworkPanelCountMap = $groupedByCategory
            ->get(MissionLimitedTermCategory::ARTWORK_PANEL->value, collect())
            ->countBy(fn (MstMissionLimitedTermEntity $entity) => $entity->getProgressGroupKey());

        return new MissionUnreceivedLimitedTermReward(
            $groupedByCategory->get(MissionLimitedTermCategory::ADVENT_BATTLE->value)?->count() ?? 0,
            $artworkPanelCountMap,
        );
    }
}
