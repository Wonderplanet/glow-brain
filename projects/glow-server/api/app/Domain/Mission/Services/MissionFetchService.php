<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Mission\Entities\MissionEventFetchStatus;
use App\Domain\Mission\Entities\MissionFetchStatus;
use App\Domain\Mission\Entities\MissionLimitedTermFetchStatus;
use App\Domain\Mission\Entities\MissionNormalFetchStatus;
use App\Domain\Mission\Enums\MissionLimitedTermCategory;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionLimitedTermInterface;
use App\Domain\Mission\Repositories\UsrMissionEventRepository;
use App\Domain\Mission\Repositories\UsrMissionLimitedTermRepository;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermEntity;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionLimitedTermRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionWeeklyRepository;
use App\Http\Responses\Data\UsrMissionBonusPointData;
use App\Http\Responses\Data\UsrMissionStatusData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionFetchService
{
    public function __construct(
        protected Clock $clock,
        // MstRepository
        protected MstMissionAchievementRepository $mstMissionAchievementRepository,
        protected MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        protected MstMissionDailyRepository $mstMissionDailyRepository,
        protected MstMissionWeeklyRepository $mstMissionWeeklyRepository,
        private MstEventRepository $mstEventRepository,
        private MstMissionEventRepository $mstMissionEventRepository,
        private MstMissionEventDailyRepository $mstMissionEventDailyRepository,
        private MstMissionLimitedTermRepository $mstMissionLimitedTermRepository,
        // UsrRepository
        protected UsrMissionNormalRepository $usrMissionNormalRepository,
        private UsrMissionEventRepository $usrMissionEventRepository,
        private UsrMissionLimitedTermRepository $usrMissionLimitedTermRepository,
        // Service
        protected MissionStatusService $missionStatusService,
        private MissionUpdateService $missionUpdateService,
    ) {
    }

    /**
     * ミッションのステータス情報を全て取得
     * 対象ミッションはusr_mission_normalsで管理されているミッションタイプのみ
     */
    public function getMissionNormalFetchStatusWhenFetchAll(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionNormalFetchStatus {
        // achievement
        $achievementMstMissions = $this->mstMissionAchievementRepository->getMapAll();
        // daily
        $dailyMstMissions = $this->mstMissionDailyRepository->getMapAll();
        // weekly
        $weeklyMstMissions = $this->mstMissionWeeklyRepository->getMapAll();
        // beginner
        $beginnerMstMissions = $this->mstMissionBeginnerRepository->getMapAll();

        // ユーザーデータ取得
        // 開放済のみレスポンスする
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: $achievementMstMissions->keys()->toArray(),
            mstMissionBeginnerIds: $beginnerMstMissions->keys()->toArray(),
            mstMissionDailyIds: $dailyMstMissions->keys()->toArray(),
            mstMissionWeeklyIds: $weeklyMstMissions->keys()->toArray(),
        )->filterForResponse();

        return new MissionNormalFetchStatus(
            $this->makeFetchStatusData(
                $now,
                MissionType::ACHIEVEMENT,
                $achievementMstMissions,
                $usrMissionNormalBundle->getAchievements(),
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::DAILY,
                $dailyMstMissions,
                $usrMissionNormalBundle->getDailies(),
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::WEEKLY,
                $weeklyMstMissions,
                $usrMissionNormalBundle->getWeeklies(),
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::BEGINNER,
                $beginnerMstMissions,
                $usrMissionNormalBundle->getBeginners(),
            ),
        );
    }

    /**
     * ミッションのステータス情報を全て取得
     * 対象ミッションはusr_mission_eventsで管理されているミッションタイプのみ
     */
    public function getMissionEventFetchStatusWhenFetchAll(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionEventFetchStatus {
        // 現在有効なイベントIDを取得
        $mstEvents = $this->mstEventRepository->getAllActiveEvents($now);
        $mstEventIds = $mstEvents->keys();

        // マスタデータ取得
        // event
        $eventMstMissions = $this->mstMissionEventRepository->getMapByMstEventIds($mstEventIds);
        // eventDaily
        $eventDailyMstMissions = $this->mstMissionEventDailyRepository->getMapByMstEventIds($mstEventIds);

        $mstMissionIds = collect([
            $eventMstMissions->keys(),
            $eventDailyMstMissions->keys(),
        ])->flatten();


        // ユーザーデータ取得（開放済のみ）
        // 開放済のみレスポンスする
        $usrMissionEventBundle = $this->usrMissionEventRepository->getByMstMissionIds(
            $usrUserId,
            $eventMstMissions->keys()->all(),
            $eventDailyMstMissions->keys()->all(),
        )->filterOpened();

        return new MissionEventFetchStatus(
            $this->makeFetchStatusData(
                $now,
                MissionType::EVENT,
                $eventMstMissions,
                $usrMissionEventBundle->getEvents(),
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::EVENT_DAILY,
                $eventDailyMstMissions,
                $usrMissionEventBundle->getEventDailies(),
            ),
        );
    }

    /**
     * ミッションのステータス情報を全て取得
     * 対象ミッションはusr_mission_limited_termsで管理されているミッションタイプのみ
     */
    public function getMissionLimitedTermFetchStatusWhenFetchAll(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionLimitedTermFetchStatus {
        $mstMissions = $this->mstMissionLimitedTermRepository->getMapAllActive($now);

        $usrMissions = $this->usrMissionLimitedTermRepository->getByMstMissionIds($usrUserId, $mstMissions->keys());

        /** @var Collection<string, Collection<string, MstMissionLimitedTermEntity>> $categoryGroupedMstMissions */
        $categoryGroupedMstMissions = $mstMissions->groupBy(
            fn (MstMissionLimitedTermEntity $mstMission) => $mstMission->getMissionCategory()
        )->map(function (Collection $mstMissions) {
            // groupByすると、idをキーとした連想配列形式が維持されないので、keyByする
            return $mstMissions->keyBy(
                fn (MstMissionLimitedTermEntity $mstMission) => $mstMission->getId()
            );
        });

        // 降臨バトルミッション(開放済のみ)
        $adventBattleMstMissions = $categoryGroupedMstMissions
            ->get(MissionLimitedTermCategory::ADVENT_BATTLE->value, collect());

        // 原画パネルミッション(開放済のみ)
        $artworkPanelMstMissions = $categoryGroupedMstMissions
            ->get(MissionLimitedTermCategory::ARTWORK_PANEL->value, collect());

        return new MissionLimitedTermFetchStatus(
            $this->makeFetchStatusData(
                $now,
                MissionType::LIMITED_TERM,
                $adventBattleMstMissions,
                $usrMissions->only($adventBattleMstMissions->keys()),
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::LIMITED_TERM,
                $artworkPanelMstMissions,
                $usrMissions->only($artworkPanelMstMissions->keys()),
            ),
        );
    }

    /**
     * ミッション報酬受取後のレスポンス用データを生成
     * 対象ミッションはusr_mission_normalsで管理されているミッションタイプのみ
     */
    public function getMissionNormalFetchStatusWhenReceiveRewards(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionNormalFetchStatus {
        // 変更があったユーザーモデルを取得
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getUsrMissionNormalBundleOfChangedModels();
        $achievementUsrMissions = $usrMissionNormalBundle->getAchievements();
        $dailyUsrMissions = $usrMissionNormalBundle->getDailies();
        $weeklyUsrMissions = $usrMissionNormalBundle->getWeeklies();
        $beginnerUsrMissions = $usrMissionNormalBundle->getBeginners();

        // マスタデータ取得
        $achievementMstMissions = $this->mstMissionAchievementRepository->getByIds($achievementUsrMissions->keys());
        $dailyMstMissions = $this->mstMissionDailyRepository->getByIdsAndBonusPoints($dailyUsrMissions->keys());
        $weeklyMstMissions = $this->mstMissionWeeklyRepository->getByIdsAndBonusPoints($weeklyUsrMissions->keys());
        $beginnerMstMissions = $this->mstMissionBeginnerRepository
            ->getByIdsAndBonusPoints($beginnerUsrMissions->keys());

        // ユーザーデータ取得
        $bonusPointUsrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(
            $usrUserId,
            mstMissionBeginnerIds: $beginnerMstMissions->keys()->all(),
            mstMissionDailyIds: $dailyMstMissions->keys()->all(),
            mstMissionWeeklyIds: $weeklyMstMissions->keys()->all(),
        );

        // レスポンス用データを作成
        // ボーナスポイント機能があるミッションタイプの場合は、データ重複しないようにmergeでCollectionを連結
        $achievementFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::ACHIEVEMENT,
            $achievementMstMissions,
            $achievementUsrMissions,
        );
        $beginnerFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::BEGINNER,
            $beginnerMstMissions,
            $beginnerUsrMissions->merge($bonusPointUsrMissionNormalBundle->getBeginners()),
        );
        $dailyFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::DAILY,
            $dailyMstMissions,
            $dailyUsrMissions->merge($bonusPointUsrMissionNormalBundle->getDailies()),
        );
        $weeklyFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::WEEKLY,
            $weeklyMstMissions,
            $weeklyUsrMissions->merge($bonusPointUsrMissionNormalBundle->getWeeklies()),
        );

        return new MissionNormalFetchStatus(
            $achievementFetchStatusData,
            $dailyFetchStatusData,
            $weeklyFetchStatusData,
            $beginnerFetchStatusData,
        );
    }

    /**
     * ミッション報酬受取後のレスポンス用データを生成
     * 対象ミッションはusr_mission_eventsで管理されているミッションタイプのみ
     */
    public function getMissionEventFetchStatusWhenReceiveRewards(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionEventFetchStatus {
        // 変更があったユーザーモデルを取得
        $usrMissionEventBundle = $this->usrMissionEventRepository->getUsrMissionEventBundleOfChangedModels();
        $eventUsrMissions = $usrMissionEventBundle->getEvents();
        $eventDailyUsrMissions = $usrMissionEventBundle->getEventDailies();


        // マスタデータ取得
        $eventMstMissions = $this->mstMissionEventRepository->getByIds($eventUsrMissions->keys());
        $eventDailyMstMissions = $this->mstMissionEventDailyRepository->getByIds($eventDailyUsrMissions->keys());

        return new MissionEventFetchStatus(
            $this->makeFetchStatusData(
                $now,
                MissionType::EVENT,
                $eventMstMissions,
                $eventUsrMissions,
            ),
            $this->makeFetchStatusData(
                $now,
                MissionType::EVENT_DAILY,
                $eventDailyMstMissions,
                $eventDailyUsrMissions,
            ),
        );
    }

    /**
     * 原画パネルミッションのステータス情報のみを取得
     * 降臨バトルミッション取得を回避してパフォーマンス改善
     */
    public function getMissionLimitedTermFetchStatusForArtworkPanel(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionFetchStatus {
        // 原画パネルカテゴリのミッションのみ取得（Repository側でフィルタ）
        $mstMissions = $this->mstMissionLimitedTermRepository->getMapAllActiveByCategory(
            $now,
            MissionLimitedTermCategory::ARTWORK_PANEL
        );

        $usrMissions = $this->usrMissionLimitedTermRepository->getByMstMissionIds($usrUserId, $mstMissions->keys());

        return $this->makeFetchStatusData(
            $now,
            MissionType::LIMITED_TERM,
            $mstMissions,
            $usrMissions,
        );
    }

    /**
     * 降臨バトルミッションのステータス情報のみを取得
     * 原画パネルミッション取得を回避してパフォーマンス改善
     */
    public function getMissionLimitedTermFetchStatusForAdventBattle(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionFetchStatus {
        // 降臨バトルカテゴリのミッションのみ取得（Repository側でフィルタ）
        $mstMissions = $this->mstMissionLimitedTermRepository->getMapAllActiveByCategory(
            $now,
            MissionLimitedTermCategory::ADVENT_BATTLE
        );

        $usrMissions = $this->usrMissionLimitedTermRepository->getByMstMissionIds($usrUserId, $mstMissions->keys());

        return $this->makeFetchStatusData(
            $now,
            MissionType::LIMITED_TERM,
            $mstMissions,
            $usrMissions,
        );
    }

    /**
     * ミッション報酬受取後のレスポンス用データを生成
     * 対象ミッションはusr_mission_limited_termsで管理されているミッションタイプのみ
     */
    public function getMissionLimitedTermFetchStatusWhenReceiveRewards(
        string $usrUserId,
        CarbonImmutable $now,
    ): MissionLimitedTermFetchStatus {
        // 変更があったユーザーモデルを取得
        /**
         * @var Collection<string, UsrMissionLimitedTermInterface> $changedUsrMissions
         * key: mst_mission_limit_term_id
         */
        $changedUsrMissions = $this->usrMissionLimitedTermRepository->getChangedModels()
            ->keyBy(fn (UsrMissionLimitedTermInterface $model) => $model->getMstMissionId());

        // マスタデータ取得
        $mstMissions = $this->mstMissionLimitedTermRepository->getByIds($changedUsrMissions->keys())
            ->groupBy(fn (MstMissionLimitedTermEntity $mstMission) => $mstMission->getMissionCategory())
            ->map(
                fn (Collection $mstMissions) => $mstMissions
                    ->keyBy(fn (MstMissionLimitedTermEntity $mstMission) => $mstMission->getId())
            );

        // レスポンス用データを作成
        /** @var Collection<string, MstMissionEntityInterface> $adventBattleMstMissions */
        $adventBattleMstMissions = $mstMissions->get(MissionLimitedTermCategory::ADVENT_BATTLE->value, collect());
        $adventBattleFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::LIMITED_TERM,
            $adventBattleMstMissions,
            $changedUsrMissions->only($adventBattleMstMissions->keys()),
        );

        /** @var Collection<string, MstMissionEntityInterface> $artworkPanelMstMissions */
        $artworkPanelMstMissions = $mstMissions->get(MissionLimitedTermCategory::ARTWORK_PANEL->value, collect());
        $artworkPanelFetchStatusData = $this->makeFetchStatusData(
            $now,
            MissionType::LIMITED_TERM,
            $artworkPanelMstMissions,
            $changedUsrMissions->only($artworkPanelMstMissions->keys()),
        );

        return new MissionLimitedTermFetchStatus(
            $adventBattleFetchStatusData,
            $artworkPanelFetchStatusData,
        );
    }

    /**
     * 指定ミッションタイプのユーザーミッションデータの内で、変更があったミッションステータス情報を取得
     */
    public function fetchChangedStatusesByMissionType(
        CarbonImmutable $now,
        MissionType $missionType,
    ): MissionFetchStatus {
        $usrMissions = $this->getChangedUsrMissionsByMissionType($missionType);

        return $this->makeFetchStatusData(
            $now,
            $missionType,
            $this->getMstMissionsByMissionTypeAndIds($missionType, $usrMissions->keys()),
            $usrMissions,
        );
    }

    /**
     * @return Collection<string, IUsrMission>
     */
    private function getChangedUsrMissionsByMissionType(
        MissionType $missionType,
    ): Collection {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
            case MissionType::BEGINNER:
            case MissionType::DAILY:
            case MissionType::WEEKLY:
                return $this->usrMissionNormalRepository->getUsrMissionNormalBundleOfChangedModels()
                    ->getByMissionType($missionType);

            case MissionType::EVENT:
            case MissionType::EVENT_DAILY:
                return $this->usrMissionEventRepository->getUsrMissionEventBundleOfChangedModels()
                    ->getByMissionType($missionType);

            case MissionType::LIMITED_TERM:
                return $this->usrMissionLimitedTermRepository->getChangedModels();

            default:
                return collect();
        }
    }

    /**
     * @return Collection<string, MstMissionEntityInterface>
     */
    private function getMstMissionsByMissionTypeAndIds(
        MissionType $missionType,
        Collection $mstMissionIds,
    ): Collection {
        return match ($missionType) {
            MissionType::ACHIEVEMENT => $this->mstMissionAchievementRepository->getByIds($mstMissionIds),
            MissionType::BEGINNER => $this->mstMissionBeginnerRepository->getByIds($mstMissionIds),
            MissionType::DAILY => $this->mstMissionDailyRepository->getByIds($mstMissionIds),
            MissionType::WEEKLY => $this->mstMissionWeeklyRepository->getByIds($mstMissionIds),
            MissionType::EVENT => $this->mstMissionEventRepository->getByIds($mstMissionIds),
            MissionType::EVENT_DAILY => $this->mstMissionEventDailyRepository->getByIds($mstMissionIds),
            MissionType::LIMITED_TERM => $this->mstMissionLimitedTermRepository->getByIds($mstMissionIds),
            default => collect(),
        };
    }

    /**
     * ミッションタイプごとのミッションステータスレスポンス用データを生成
     * リセットが必要な状態の場合は、リセットしたモデルを返す
     *
     * @param Collection<string, covariant MstMissionEntityInterface> $mstMissions key: mst_mission_id
     * @param Collection<IUsrMission> $usrMissions
     *        変更があったモデルと、ボーナスポイントミッションのユーザーモデル全てを含む Collection
     */
    private function makeFetchStatusData(
        CarbonImmutable $now,
        MissionType $missionType,
        Collection $mstMissions,
        Collection $usrMissions,
    ): MissionFetchStatus {
        // レスポンス用にDB更新なしで進捗をリセットする。
        $usrMissions = $this->missionUpdateService->resetUsrMissionsByMissionType(
            $missionType,
            $usrMissions,
            $mstMissions,
            $now,
        );

        $usrStatusDataList = collect();
        $receivedBonusPointRewardPoints = collect();
        $usrBonusPoint = 0;

        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */
            $mstMission = $mstMissions->get($usrMission->getMstMissionId());
            if ($mstMission === null) {
                continue;
            }
            /** @var MstMissionEntityInterface $mstMission */

            if ($mstMission->isBonusPointMission()) {
                // 獲得済ボーナスポイントを取得
                // 複数のユーザーミッションデータで進捗値が記録されているので、最大値を取得する
                $usrBonusPoint = max($usrBonusPoint, $usrMission->getProgress());

                // ボーナスポイントミッションの内で、報酬受取済ミッションのボーナスポイントを取得
                if ($usrMission->isReceivedReward()) {
                    $receivedBonusPointRewardPoints->push($mstMission->getCriterionCount());
                }
            } else {
                // ボーナスポイント以外のミッションステータスレスポンス用データを生成
                $usrStatusDataList->push(
                    new UsrMissionStatusData(
                        $mstMission->getId(),
                        $usrMission->getProgress(),
                        $usrMission->isClear(),
                        $usrMission->isReceivedReward(),
                        $mstMission->getResponseGroupId(),
                    )
                );
            }
        }

        $usrBonusPointData = new UsrMissionBonusPointData(
            $missionType->value,
            $usrBonusPoint,
            $receivedBonusPointRewardPoints->values(),
        );

        return new MissionFetchStatus(
            $usrStatusDataList,
            $usrBonusPointData,
        );
    }
}
