<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\Clock;
use App\Domain\Mission\Entities\Criteria\MissionCriterion;
use App\Domain\Mission\Entities\MissionChain;
use App\Domain\Mission\Entities\MissionCompositeClearCount;
use App\Domain\Mission\Entities\MissionState;
use App\Domain\Mission\Entities\MissionUpdateBundle;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Factories\MissionCriterionFactory;
use App\Domain\Mission\Factories\MissionUpdateEntityFactory;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Models\IUsrMission;
use App\Domain\Mission\Models\UsrMissionEventInterface;
use App\Domain\Mission\Repositories\UsrMissionEventRepository;
use App\Domain\Mission\Repositories\UsrMissionLimitedTermRepository;
use App\Domain\Mission\Repositories\UsrMissionNormalRepository;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface;
use App\Domain\Resource\Mst\Entities\MstMissionEventEntity;
use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermEntity;
use App\Domain\Resource\Mst\Repositories\MstEventRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MissionUpdateService
{
    public function __construct(
        protected MissionManager $missionManager,
        protected MissionStatusService $missionStatusService,
        protected MissionCriterionFactory $missionCriterionFactory,
        private MissionUpdateEntityFactory $missionUpdateEntityFactory,
        // MstRepository
        protected MstEventRepository $mstEventRepository,
        // UsrRepository
        protected UsrMissionNormalRepository $usrMissionNormalRepository,
        private UsrMissionEventRepository $usrMissionEventRepository,
        protected UsrMissionLimitedTermRepository $usrMissionLimitedTermRepository,
        protected UsrMissionStatusRepository $usrMissionStatusRepository,
        // Common
        protected Clock $clock,
    ) {
    }

    public function updateTriggeredMissions(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        // マスタデータを取得しつつ進捗判定に必要なMissionドメインのEntityを生成していく
        // normal
        $achievementBundle = $this->missionUpdateEntityFactory->createAchievementMissionUpdateBundle();
        $beginnerBundle = $this->missionUpdateEntityFactory->createBeginnerMissionUpdateBundle($usrUserId);
        $dailyBundle = $this->missionUpdateEntityFactory->createDailyMissionUpdateBundle();
        $weeklyBundle = $this->missionUpdateEntityFactory->createWeeklyMissionUpdateBundle();
        // event
        $mstEvents = $this->mstEventRepository->getAllActiveEvents($now);
        $mstEventIds = $mstEvents->keys();
        $eventBundle = $this->missionUpdateEntityFactory->createEventMissionUpdateBundle($mstEventIds);
        $eventDailyBundle = $this->missionUpdateEntityFactory->createEventDailyMissionUpdateBundle($mstEventIds);
        // limited term
        $limitedTermBundle = $this->missionUpdateEntityFactory->createLimitedTermMissionUpdateBundle($now);

        // ユーザーデータ取得
        // normal
        $usrMissionNormalBundle = $this->usrMissionNormalRepository->getByMstMissionIds(
            $usrUserId,
            mstMissionAchievementIds: $achievementBundle?->getMstMissionIds()->all() ?? [],
            mstMissionBeginnerIds: $beginnerBundle?->getMstMissionIds()->all() ?? [],
            mstMissionDailyIds: $dailyBundle?->getMstMissionIds()->all() ?? [],
            mstMissionWeeklyIds: $weeklyBundle?->getMstMissionIds()->all() ?? [],
        );
        // event
        $usrMissionEventBundle = $this->usrMissionEventRepository->getByMstMissionIds(
            $usrUserId,
            $eventBundle?->getMstMissionIds()->all() ?? [],
            $eventDailyBundle?->getMstMissionIds()->all() ?? [],
        );

        // ユーザーデータをセットして、各ミッションタイプごとに、進捗判定と更新を進める

        // achievement
        if ($achievementBundle !== null) {
            $achievementBundle->setUsrMissions($usrMissionNormalBundle->getAchievements());
            $this->updateMissions($usrUserId, $now, $achievementBundle);
        }

        // beginner
        if ($beginnerBundle !== null) {
            $beginnerBundle->setUsrMissions($usrMissionNormalBundle->getBeginners());
            $this->updateMissions($usrUserId, $now, $beginnerBundle);
        }

        // daily
        if ($dailyBundle !== null) {
            $dailyBundle->setUsrMissions(
                $this->resetDailyUsrMissions(
                    $usrMissionNormalBundle->getDailies(),
                    $now,
                )
            );
            $this->updateMissions($usrUserId, $now, $dailyBundle);
        }

        // weekly
        if ($weeklyBundle !== null) {
            $weeklyBundle->setUsrMissions(
                $this->resetWeeklyUsrMissions(
                    $usrMissionNormalBundle->getWeeklies(),
                    $now,
                )
            );
            $this->updateMissions($usrUserId, $now, $weeklyBundle);
        }

        // event
        if ($eventBundle !== null) {
            $eventBundle->setUsrMissions(
                $this->resetEventUsrMissions(
                    $usrMissionEventBundle->getEvents(),
                    $eventBundle->getMstMissions(),
                    $now,
                )
            );
            $this->updateMissions($usrUserId, $now, $eventBundle);
        }

        // event daily
        if ($eventDailyBundle !== null) {
            $eventDailyBundle->setUsrMissions(
                $this->resetEventDailyUsrMissions(
                    $usrMissionEventBundle->getEventDailies(),
                    $now,
                )
            );
            $this->updateMissions($usrUserId, $now, $eventDailyBundle);
        }

        // limited term
        if ($limitedTermBundle !== null) {
            $usrMissionLimitedTerms = $this->usrMissionLimitedTermRepository->getByMstMissionIds(
                $usrUserId,
                $limitedTermBundle->getMstMissionIds(),
            );

            $limitedTermBundle->setUsrMissions(
                $this->resetLimitedTermUsrMissions(
                    $usrMissionLimitedTerms,
                    $limitedTermBundle->getMstMissions(),
                    $now,
                )
            );
            $this->updateMissions($usrUserId, $now, $limitedTermBundle);
        }
    }

    private function updateMissions(
        string $usrUserId,
        CarbonImmutable $now,
        MissionUpdateBundle $bundle,
    ): void {
        $states = $this->createStates($bundle);
        $bundle->setStates($states);
        $chains = $this->createChains($bundle);

        /**
         * 達成判定
         *
         * 未開放でも達成進捗値は進める必要があるので、開放判定前に達成判定を行う
         */
        // 進捗変動があったミッションの達成判定
        $this->checkStatesClear($states);

        // 複合ミッションの進捗値を更新
        $this->updateCompositeCriterionProgresses($states);

        /**
         * 開放判定
         */
        // 進捗変動があったミッションの開放判定
        $this->checkStatesOpen($states);
        // 新規開放され初完了したミッション分を複合ミッションの進捗値に反映
        $this->updateCompositeCriterionProgresses($states);

        /**
         * 依存関係があるミッションの開放達成判定
         *
         * MissionChainを生成し、段階的な開放判定を行う
         */
        // chain内の複合ミッションの未クリア数+1が、段階的な開放判定のループ最大回数となる
        if ($bundle->hasDependency()) {
            $unclearCompositeMissionCount = $chains
                ->sum(fn(MissionChain $chain) => $chain->calcUnclearCompositeMissionCount());

            // 開放判定を実行するために、最低でも1ループ必要
            $checkLoopCount = $unclearCompositeMissionCount + 1;

            for ($i = 0; $i < $checkLoopCount; $i++) {
                $isAllFinalized = $this->stepChainsForOpen($chains);
                // 新規で開放され、複合ミッションの進捗が変動する場合があるので、複合ミッションの進捗値を更新
                $this->updateCompositeCriterionProgresses($states);

                if ($isAllFinalized) {
                    break;
                }
            }
        }

        // 進捗更新反映
        $this->updateUsrMission(
            $usrUserId,
            $now,
            $bundle->getMissionType()->getIntValue(),
            $states,
        );
    }

    /**
     * 複合ミッションの進捗値に加算する値を算出する
     *
     * @param Collection<MissionState> $states
     */
    private function calcCompositeMissionProgressAdditions(Collection $states): MissionCompositeClearCount
    {
        $allClearCount = 0;
        $groupClearCounts = collect();
        foreach ($states as $state) {
            /** @var \App\Domain\Mission\Entities\MissionState $state */
            if ($state->isAddableCompositeMissionProgress() === false) {
                continue;
            }

            $allClearCount++;

            $mstGroupKey = $state->getMstMission()->getGroupKey();
            if ($mstGroupKey !== null) {
                $groupClearCounts->put(
                    $mstGroupKey,
                    $groupClearCounts->get($mstGroupKey, 0) + 1
                );
            }

            $state->markAddedCompositeMissionProgress();
        }

        return new MissionCompositeClearCount(
            $allClearCount, // 全体の初クリア数
            $groupClearCounts, // グループごとの初クリア数
        );
    }

    /**
     * 複合ミッションの進捗値を更新し、複合ミッションの達成判定を行う
     * 他のミッションの新規達成数が進捗値に影響するため、定期的に実行し、進捗値を更新する。
     *
     * @param Collection<MissionState> $states
     * @return void
     */
    private function updateCompositeCriterionProgresses(
        Collection $states,
    ): void {
        if ($states->isEmpty()) {
            return;
        }

        $firstCompositeClearCountData = $this->calcCompositeMissionProgressAdditions($states);

        foreach ($states as $state) {
            /** @var MissionState $state */
            if ($state->isCompositeMission() === false) {
                continue;
            }

            $criterionType = $state->getMstMission()->getCriterionType();
            $criterionValue = $state->getMstMission()->getCriterionValue();

            if ($criterionType === MissionCriterionType::MISSION_CLEAR_COUNT->value) {
                $progress = $firstCompositeClearCountData->getAllClearCount();
            } elseif ($criterionType === MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT->value) {
                $progress = $firstCompositeClearCountData->getGroupClearCount(
                    $criterionValue,
                );
            } else {
                // 複合ミッションではないので進捗値変更せずにスキップ
                continue;
            }

            $state->getCriterion()->aggregateProgress($progress);

            // 達成判定
            $state->checkAndClear();
        }
    }

    /**
     * 進捗更新ステータスをユーザーモデルへ反映する
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @param int $missionType MissionType enum getIntValue()で取得した数値
     * @param Collection<MissionState> $states
     */
    public function updateUsrMission(
        string $usrUserId,
        CarbonImmutable $now,
        int $missionType,
        Collection $states,
    ): void {
        if ($states->isEmpty()) {
            return;
        }
        $missionTypeEnum = MissionType::getFromInt($missionType);

        $usrMissions = collect();

        foreach ($states as $state) {
            /** @var \App\Domain\Mission\Entities\MissionState $state */
            if ($state->isUpdateNotNeeded()) {
                continue;
            }

            $mstMissionId = $state->getMstMissionId();

            $usrMission = $state->getUsrMission();
            if (is_null($usrMission)) {
                // 進捗変更があったミッションなので、レコードがない場合は新規作成
                $usrMission = $this->createUsrMission(
                    $usrUserId,
                    $missionTypeEnum,
                    $mstMissionId,
                    $now,
                );
                if ($usrMission === null) {
                    continue;
                }
            }

            // 変更の反映
            // 達成進捗
            $usrMission->updateProgress($state->getProgress());
            $state->isClear() && $usrMission->clear($now);

            // 開放進捗
            $usrMission->updateUnlockProgress($state->getUnlockProgress());
            $state->isOpen() && $usrMission->open();

            $usrMissions->push($usrMission);
        }
        $this->syncUsrMissions($missionTypeEnum, $usrMissions);
    }

    private function createUsrMission(
        string $usrUserId,
        MissionType $missionType,
        string $mstMissionId,
        CarbonImmutable $now,
    ): ?IUsrMission {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
            case MissionType::BEGINNER:
            case MissionType::DAILY:
            case MissionType::WEEKLY:
                return $this->usrMissionNormalRepository->create(
                    $usrUserId,
                    $missionType->getIntValue(),
                    $mstMissionId,
                    $now,
                );
            case MissionType::EVENT:
            case MissionType::EVENT_DAILY:
                return $this->usrMissionEventRepository->create(
                    $usrUserId,
                    $missionType->getIntValue(),
                    $mstMissionId,
                    $now,
                );
            case MissionType::LIMITED_TERM:
                return $this->usrMissionLimitedTermRepository->create(
                    $usrUserId,
                    $mstMissionId,
                    $now,
                );
            default:
                return null;
        }
    }

    private function syncUsrMissions(MissionType $missionType, Collection $usrMissions): void
    {
        switch ($missionType) {
            case MissionType::ACHIEVEMENT:
            case MissionType::BEGINNER:
            case MissionType::DAILY:
            case MissionType::WEEKLY:
                $this->usrMissionNormalRepository->syncModels($usrMissions);
                break;
            case MissionType::EVENT:
            case MissionType::EVENT_DAILY:
                $this->usrMissionEventRepository->syncModels($usrMissions);
                break;
            case MissionType::LIMITED_TERM:
                $this->usrMissionLimitedTermRepository->syncModels($usrMissions);
                break;
        }
    }

    private function checkStatesClear(Collection $states): void
    {
        foreach ($states as $state) {
            /** @var MissionState $state */
            $state->checkAndClear();
        }
    }

    private function checkStatesOpen(Collection $states): void
    {
        foreach ($states as $state) {
            /** @var MissionState $state */
            $state->checkAndOpen();
        }
    }

    private function stepChainsForOpen(Collection $chains): bool
    {
        $isAllFinalized = true;
        foreach ($chains as $chain) {
            /** @var MissionChain $chain */
            $isAllFinalized = $chain->stepForOpen() && $isAllFinalized;
        }

        return $isAllFinalized;
    }

    /**
     * @return Collection<MissionState>
     */
    public function createStates(
        MissionUpdateBundle $bundle,
    ): Collection {
        $mstMissions = $bundle->getMstMissions();
        $usrMissions = $bundle->getUsrMissions();
        $newProgressCriteria = $bundle->getCriteria();

        $states = collect();
        foreach ($mstMissions as $mstMission) {
            /** @var \App\Domain\Resource\Mst\Entities\Contracts\MstMissionEntityInterface $mstMission */
            /** @var IUsrMission|null $usrMission */
            $usrMission = $usrMissions->get($mstMission->getId());

            // 既存進捗値のcriterionを作ってから、新規進捗値を集約する

            /**
             * 達成条件設定
             */
            // 既存進捗値(usrMissionから取得)のcriterionを作成
            $criterion = $this->missionCriterionFactory->createMissionCriterion(
                $mstMission->getCriterionType(),
                $mstMission->getCriterionValue(),
                $usrMission?->getProgress(),
            );
            if ($criterion === null) {
                // 達成判定がないミッションはスキップ
                continue;
            }
            /** @var MissionCriterion $criterion */
            $newProgressCriterion = $newProgressCriteria->get($mstMission->getCriterionKey());
            if ($newProgressCriterion !== null) {
                // 新規進捗値を集約
                $criterion->aggregateProgress($newProgressCriterion->getProgress());
            }

            /**
             * 開放条件設定
             */
            $unlockCriterion = null;
            if ($mstMission->hasUnlockCriterion()) {
                // 既存進捗値(usrMissionから取得)のcriterionを作成
                $unlockCriterion = $this->missionCriterionFactory->createMissionCriterion(
                    $mstMission->getUnlockCriterionType(),
                    $mstMission->getUnlockCriterionValue(),
                    $usrMission?->getUnlockProgress(),
                );
                $newProgressCriterion = $newProgressCriteria->get($mstMission->getUnlockCriterionKey());
                if ($newProgressCriterion !== null) {
                    // 新規進捗値を集約
                    $unlockCriterion?->aggregateProgress($newProgressCriterion->getProgress());
                }
            }

            $states->push(
                new MissionState(
                    $mstMission,
                    $usrMission,
                    $criterion,
                    $unlockCriterion,
                ),
            );
        }

        return $states;
    }

    private function createChains(
        MissionUpdateBundle $bundle,
    ): Collection {
        if ($bundle->hasDependency() === false) {
            return collect();
        }

        $chains = collect();
        $groupedMstDepends = $bundle->getMstMissionDependencies()->groupBy->getGroupId();
        $usrMissions = $bundle->getUsrMissions();
        $states = $bundle->getStates()->keyBy(fn(MissionState $state) => $state->getMstMissionId());

        foreach ($groupedMstDepends as $groupId => $mstDepends) {
            $mstDepends = $mstDepends->sortBy->getUnlockOrder();

            $sortedStates = collect();
            foreach ($mstDepends as $mstDepend) {
                $mstMissionId = $mstDepend->getMstMissionId();
                $usrMission = $usrMissions->get($mstMissionId);
                $state = $states->get($mstMissionId);

                if ($usrMission?->isOpen()) {
                    // すでに開放済み
                    continue;
                } elseif ($state === null) {
                    /**
                     * stateがない = 進捗変動がないミッション
                     * 未開放で、トリガーもされておらず、進捗変動なし
                     * これ以上開放はできないので、次の依存関係グループのchain生成へ
                     */
                    break;
                } else {
                    // 依存関係グループに属するミッションは、1つ前のミッションを完了(開放済かつ達成済)しないと開放されない
                    // よって、依存関係グループ起因のロックを行ってから、MissionChainに追加する
                    $state->dependencyLock();

                    // 未解放だが、トリガーされていて、進捗変動あり
                    $sortedStates->push($state);
                }
            }

            $chains->push(new MissionChain($sortedStates));
        }

        return $chains;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @return Collection<IUsrMission> リセット処理をした後のユーザーデータ
     */
    public function resetDailyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
    {
        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */
            if (
                $usrMission->getMissionType() === MissionType::DAILY->getIntValue()
                && $this->clock->isFirstToday($usrMission->getLatestResetAt())
            ) {
                $usrMission->reset($now);
            }
        }

        return $usrMissions;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @return Collection<IUsrMission> リセット処理をした後のユーザーデータ
     */
    public function resetWeeklyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
    {
        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */
            if (
                $usrMission->getMissionType() === MissionType::WEEKLY->getIntValue()
                && $this->clock->isFirstWeek($usrMission->getLatestResetAt())
            ) {
                $usrMission->reset($now);
            }
        }

        return $usrMissions;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @param Collection<MstMissionEntityInterface> $mstMissions
     * @return Collection<IUsrMission> リセット処理をした後のユーザーデータ
     */
    public function resetLimitedTermUsrMissions(
        Collection $usrMissions,
        Collection $mstMissions,
        CarbonImmutable $now,
    ): Collection {
        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */
            if ($usrMission->getMissionType() !== MissionType::LIMITED_TERM->getIntValue()) {
                continue;
            }

            $mstMission = $mstMissions->get($usrMission->getMstMissionId());
            if ($mstMission === null) {
                continue;
            }
            /** @var MstMissionLimitedTermEntity $mstMission */

            if ($usrMission->getLatestResetAt() < $mstMission->getStartAt()) {
                $usrMission->reset($now);
            }
        }

        return $usrMissions;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @param Collection<MstMissionEntityInterface> $mstMissions
     * @return Collection<IUsrMission> リセット処理をした後のユーザーデータ
     */
    public function resetEventUsrMissions(
        Collection $usrMissions,
        Collection $mstMissions,
        CarbonImmutable $now,
    ): Collection {
        foreach ($usrMissions as $usrMission) {
            /** @var UsrMissionEventInterface $usrMission */
            if ($usrMission->getMissionType() !== MissionType::EVENT->getIntValue()) {
                continue;
            }

            $mstMission = $mstMissions->get($usrMission->getMstMissionId());
            if ($mstMission === null) {
                continue;
            }
            /** @var MstMissionEventEntity $mstMission */

            if ($usrMission->getLatestResetAt() < $mstMission->getStartAt()) {
                $usrMission->reset($now);
            }
        }

        return $usrMissions;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @return Collection<IUsrMission> リセット処理をした後のユーザーデータ
     */
    public function resetEventDailyUsrMissions(Collection $usrMissions, CarbonImmutable $now): Collection
    {
        foreach ($usrMissions as $usrMission) {
            /** @var IUsrMission $usrMission */
            if (
                $usrMission->getMissionType() === MissionType::EVENT_DAILY->getIntValue()
                && $this->clock->isFirstToday($usrMission->getLatestResetAt())
            ) {
                $usrMission->reset($now);
            }
        }

        return $usrMissions;
    }

    /**
     * @param Collection<IUsrMission> $usrMissions
     * @param Collection<MstMissionEntityInterface> $mstMissions
     * @param \Carbon\CarbonImmutable $now
     * @return Collection<IUsrMission>
     */
    public function resetUsrMissionsByMissionType(
        MissionType $missionType,
        Collection $usrMissions,
        Collection $mstMissions,
        CarbonImmutable $now,
    ): Collection {
        return match ($missionType) {
            MissionType::DAILY => $this->resetDailyUsrMissions(
                $usrMissions,
                $now,
            ),
            MissionType::WEEKLY => $this->resetWeeklyUsrMissions(
                $usrMissions,
                $now,
            ),
            MissionType::EVENT => $this->resetEventUsrMissions(
                $usrMissions,
                $mstMissions,
                $now,
            ),
            MissionType::EVENT_DAILY => $this->resetEventDailyUsrMissions(
                $usrMissions,
                $now,
            ),
            MissionType::LIMITED_TERM => $this->resetLimitedTermUsrMissions(
                $usrMissions,
                $mstMissions,
                $now,
            ),
            default => $usrMissions, // リセットなし
        };
    }
}
