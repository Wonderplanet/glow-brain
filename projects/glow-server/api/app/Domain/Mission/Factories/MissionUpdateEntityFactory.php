<?php

declare(strict_types=1);

namespace App\Domain\Mission\Factories;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Entities\Criteria\MissionCriterion;
use App\Domain\Mission\Entities\MissionUpdateBundle;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\MissionManager;
use App\Domain\Mission\Repositories\UsrMissionStatusRepository;
use App\Domain\Mission\Services\MissionStatusService;
use App\Domain\Resource\Mst\Entities\MstMissionAchievementDependencyEntity;
use App\Domain\Resource\Mst\Entities\MstMissionBeginnerEntity;
use App\Domain\Resource\Mst\Entities\MstMissionEventDependencyEntity;
use App\Domain\Resource\Mst\Entities\MstMissionLimitedTermDependencyEntity;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementDependencyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionBeginnerRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDailyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventDependencyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionEventRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionLimitedTermDependencyRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionLimitedTermRepository;
use App\Domain\Resource\Mst\Repositories\MstMissionWeeklyRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * ミッションの進捗判定処理で使用するMissionドメインのEntityを生成するFactory
 */
class MissionUpdateEntityFactory
{
    public function __construct(
        private MissionManager $missionManager,
        // Factory
        private MissionCriterionFactory $missionCriterionFactory,
        // Service
        private MissionStatusService $missionStatusService,
        // UsrRepository
        private UsrMissionStatusRepository $usrMissionStatusRepository,
        // ミッションタイプごとに必要なDI
        // achievement
        private MstMissionAchievementRepository $mstMissionAchievementRepository,
        private MstMissionAchievementDependencyRepository $mstMissionAchievementDependencyRepository,
        // beginner
        private MstMissionBeginnerRepository $mstMissionBeginnerRepository,
        // daily
        private MstMissionDailyRepository $mstMissionDailyRepository,
        // weekly
        private MstMissionWeeklyRepository $mstMissionWeeklyRepository,
        // event
        private MstMissionEventRepository $mstMissionEventRepository,
        private MstMissionEventDependencyRepository $mstMissionEventDependencyRepository,
        // event daily
        private MstMissionEventDailyRepository $mstMissionEventDailyRepository,
        // limited term
        private MstMissionLimitedTermRepository $mstMissionLimitedTermRepository,
        private MstMissionLimitedTermDependencyRepository $mstMissionLimitedTermDependencyRepository,
    ) {
    }

    public function createAchievementMissionUpdateBundle(): ?MissionUpdateBundle
    {
        $missionType = MissionType::ACHIEVEMENT;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionAchievementRepository->getTriggeredMissionsByCriteria($criteria)->keyBy->getId();
        if ($msts->isEmpty()) {
            return null;
        }
        $mstIds = $msts->mapWithKeys(fn($mst) => [$mst->getId() => $mst->getId()]);

        // 依存関係マスタデータ取得
        $mstDepends = $this->mstMissionAchievementDependencyRepository
            ->getSameGroupsByMstMissionIds($mstIds);
        $hasDependMstMissionIds = $mstDepends
            ->mapWithKeys(function (MstMissionAchievementDependencyEntity $mst) {
                return [$mst->getMstMissionId() => $mst->getMstMissionId()];
            });
        $allMstMissionIds = $mstIds->merge($hasDependMstMissionIds);

        // 同じ依存関係グループに属する未取得のマスタデータを取得
        $sameGroupMstIds = $allMstMissionIds->keys()->diff($mstIds->keys())->values();
        $sameGroupMsts = $this->mstMissionAchievementRepository->getByIds($sameGroupMstIds);

        // 進捗更新対象のマスタデータをまとめる
        $msts = $msts->merge($sameGroupMsts);

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            $mstDepends,
            $criteria,
        );
    }

    public function createDailyMissionUpdateBundle(): ?MissionUpdateBundle
    {
        $missionType = MissionType::DAILY;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionDailyRepository->getTriggeredMissionsByCriteria($criteria)->keyBy->getId();
        if ($msts->isEmpty()) {
            return null;
        }

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            null,
            $criteria,
        );
    }

    public function createWeeklyMissionUpdateBundle(): ?MissionUpdateBundle
    {
        $missionType = MissionType::WEEKLY;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionWeeklyRepository->getTriggeredMissionsByCriteria($criteria)->keyBy->getId();
        if ($msts->isEmpty()) {
            return null;
        }

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            null,
            $criteria,
        );
    }

    public function createBeginnerMissionUpdateBundle(string $usrUserId): ?MissionUpdateBundle
    {
        $missionType = MissionType::BEGINNER;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        // 初心者ミッションの更新が必要か確認
        $usrMissionStatus = $this->usrMissionStatusRepository->get($usrUserId);
        $isBeginnerUpdateRequired = $this->missionStatusService->isBeginnerMissionUpdateRequired(
            $usrMissionStatus,
        );
        if ($isBeginnerUpdateRequired === false) {
            // 初心者ミッションを全完了済で、更新が不要なので終了
            return null;
        }

        // 初心者ミッションの開放条件確認のためのトリガーを追加
        $beginnerUnlockedDays = $this->missionStatusService->calcDaysFromMissionUnlockedAt(
            $usrMissionStatus,
        );
        $triggers->push(
            new MissionTrigger(
                MissionCriterionType::DAYS_FROM_UNLOCKED_MISSION->value,
                null,
                $beginnerUnlockedDays,
            ),
        );

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionBeginnerRepository->getTriggeredMissionsByCriteria($criteria)
            ->keyBy(fn(MstMissionBeginnerEntity $mst) => $mst->getId());
        if ($msts->isEmpty()) {
            return null;
        }

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            null,
            $criteria,
        );
    }

    public function createEventMissionUpdateBundle(Collection $mstEventIds): ?MissionUpdateBundle
    {
        $missionType = MissionType::EVENT;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionEventRepository
            ->getTriggeredMissionsByCriteriaAndMstEventIds($criteria, $mstEventIds);
        if ($msts->isEmpty()) {
            return null;
        }
        $mstIds = $msts->mapWithKeys(fn($mst) => [$mst->getId() => $mst->getId()]);

        // 依存関係マスタデータ取得
        $mstDepends = $this->mstMissionEventDependencyRepository->getSameGroupsByMstMissionIds($mstIds);
        $hasDependMstMissionIds = $mstDepends
            ->mapWithKeys(function (MstMissionEventDependencyEntity $mst) {
                return [$mst->getMstMissionId() => $mst->getMstMissionId()];
            });
        $allMstMissionIds = $mstIds->merge($hasDependMstMissionIds);

        // 同じ依存関係グループに属する未取得のマスタデータを取得
        $sameGroupMstIds = $allMstMissionIds->keys()->diff($mstIds->keys())->values();
        $sameGroupMsts = $this->mstMissionEventRepository->getByIds($sameGroupMstIds);

        // 進捗更新対象のマスタデータをまとめる
        $msts = $msts->merge($sameGroupMsts);

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            $mstDepends,
            $criteria,
        );
    }

    public function createEventDailyMissionUpdateBundle(Collection $mstEventIds): ?MissionUpdateBundle
    {
        $missionType = MissionType::EVENT_DAILY;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionEventDailyRepository
            ->getTriggeredMissionsByCriteriaAndMstEventIds($criteria, $mstEventIds);
        if ($msts->isEmpty()) {
            return null;
        }
        $mstIds = $msts->mapWithKeys(fn($mst) => [$mst->getId() => $mst->getId()]);

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            null,
            $criteria,
        );
    }

    public function createLimitedTermMissionUpdateBundle(CarbonImmutable $now): ?MissionUpdateBundle
    {
        $missionType = MissionType::LIMITED_TERM;

        // トリガー取得
        /** @var MissionType $missionType */
        $triggers = $this->missionManager->popTriggers($missionType);
        if ($triggers->isEmpty()) {
            return null;
        }

        $criteria = $this->createAggregatedProgressCriteriaByTriggers($triggers);

        // マスタデータ取得（トリガーされたミッションと、複合ミッションを含む）
        $msts = $this->mstMissionLimitedTermRepository
            ->getTriggeredActiveMissionsByCriteria($criteria, $now)
            ->keyBy->getId();
        if ($msts->isEmpty()) {
            return null;
        }
        $mstIds = $msts->mapWithKeys(fn($mst) => [$mst->getId() => $mst->getId()]);

        // 依存関係マスタデータ取得
        $mstDepends = $this->mstMissionLimitedTermDependencyRepository->getSameGroupsByMstMissionIds($mstIds);
        $hasDependMstMissionIds = $mstDepends
            ->mapWithKeys(function (MstMissionLimitedTermDependencyEntity $mst) {
                return [$mst->getMstMissionId() => $mst->getMstMissionId()];
            });
        $allMstMissionIds = $mstIds->merge($hasDependMstMissionIds);

        // 同じ依存関係グループに属する未取得のマスタデータを取得
        $sameGroupMstIds = $allMstMissionIds->keys()->diff($mstIds->keys())->values();
        $sameGroupMsts = $this->mstMissionLimitedTermRepository->getByIds($sameGroupMstIds);

        // 進捗更新対象のマスタデータをまとめる
        $msts = $msts->merge($sameGroupMsts);

        return new MissionUpdateBundle(
            $missionType,
            $msts,
            $mstDepends,
            $criteria,
        );
    }

    /**
     * トリガーから進捗値を集約したMissionCriterionを生成する
     *
     * @return Collection<string, MissionCriterion> key: criterion_key
     */
    private function createAggregatedProgressCriteriaByTriggers(Collection $triggers): Collection
    {
        $criteria = collect();
        foreach ($triggers as $trigger) {
            /** @var MissionTrigger $trigger */
            $criterionKey = $trigger->getCriterionKey();

            $criterion = $criteria->get($criterionKey);
            if (is_null($criterion)) {
                $criterion = $this->missionCriterionFactory->createMissionCriterion(
                    $trigger->getCriterionType(),
                    $trigger->getCriterionValue(),
                );
            }

            // トリガー進捗値を集約
            $criterion->aggregateProgress($trigger->getProgress());

            $criteria->put($criterionKey, $criterion);
        }

        return $criteria;
    }
}
