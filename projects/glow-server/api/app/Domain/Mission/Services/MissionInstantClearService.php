<?php

declare(strict_types=1);

namespace App\Domain\Mission\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\MissionManager;
use App\Domain\Resource\Mst\Repositories\MstMissionAchievementRepository;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Delegators\UnitDelegator;
use Carbon\CarbonImmutable;

/**
 * ミッションの即時達成判定を行うサービス
 */
class MissionInstantClearService
{
    public function __construct(
        private MissionManager $missionManager,
        private MissionStatusService $missionStatusService,
        private MstMissionAchievementRepository $mstMissionAchievementRepository,
        private MissionUpdateService $missionUpdateService,
        // External Domain Class
        private UnitDelegator $unitDelegator,
    ) {
    }

    /**
     * 即時達成判定が必要か判定して、必要なら即時達成対象のトリガーを送信し、進捗判定を実行する
     */
    public function execInstantClear(
        string $usrUserId,
        CarbonImmutable $now,
    ): void {
        if ($this->missionStatusService->needInstantClear($usrUserId, $now) === false) {
            return;
        }

        $needInstantClear = $this->sendUnitTriggerForInstanceClear(
            $usrUserId,
        );

        // 即時達成判定を実行したマスタデータハッシュを更新
        $this->missionStatusService->updateLatestMstHash($usrUserId, $now);

        // 何もトリガーされていないなら、判定不要なので、終了
        if (!$needInstantClear) {
            return;
        }

        $this->missionUpdateService->updateTriggeredMissions(
            $usrUserId,
            $now,
        );
    }

    /**
     * 即時達成判定のための、ユニット関連のトリガーを送信する
     *
     * @return bool true: 1つ以上のトリガーが送信された, false: トリガー未送信
     */
    private function sendUnitTriggerForInstanceClear(
        string $usrUserId,
    ): bool {
        $mstMissionAchievements = $this->mstMissionAchievementRepository
            ->getByCriterionTypes(
                MissionCriterionType::needInstantClearTypes()->map->value,
            );

        /** @var \Illuminate\Support\Collection<string> $mstUnitIds 即時達成判定対象のマスタユニットID配列(重複なし) */
        $mstUnitIds = collect();
        $criterionTypeMstUnitIdsMap = [];
        foreach ($mstMissionAchievements as $mstMissionAchievement) {
            /** @var \App\Domain\Resource\Mst\Entities\MstMissionAchievementEntity $mstMissionAchievement */
            $criterionType = $mstMissionAchievement->getCriterionType();
            $criterionValue = $mstMissionAchievement->getCriterionValue();

            if ($mstMissionAchievement->hasCriterionValue()) {
                $mstUnitIds->push($criterionValue);
            }

            // criterion_valueの重複なしで配列へ格納
            $criterionTypeMstUnitIdsMap[$criterionType][$criterionValue] = $criterionValue;
        }
        $mstUnitIds = $mstUnitIds->unique();

        if ($mstUnitIds->isEmpty()) {
            return false;
        }
        $usrUnits = $this->unitDelegator->getByMstUnitIds($usrUserId, $mstUnitIds);
        if ($usrUnits->isEmpty()) {
            return false;
        }

        foreach ($criterionTypeMstUnitIdsMap as $criterionType => $targetMstUnitIds) {
            foreach ($targetMstUnitIds as $targetMstUnitId) {
                /** @var UsrUnitEntity|null $usrUnit */
                $usrUnit = $usrUnits->get($targetMstUnitId);
                if (is_null($usrUnit)) {
                    // 対象ユニット未所持の場合はトリガーしない
                    continue;
                }

                $progress = match ($criterionType) {
                    MissionCriterionType::SPECIFIC_UNIT_LEVEL->value => $usrUnit->getLevel(),
                    MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT->value => $usrUnit->getRank(),
                    MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT->value => $usrUnit->getGradeLevel(),
                    default => null,
                };
                if (is_null($progress)) {
                    // 想定しないタイプの場合はトリガーしない
                    continue;
                }

                $this->missionManager->addTrigger(
                    new MissionTrigger(
                        $criterionType,
                        $targetMstUnitId,
                        $progress,
                    ),
                    MissionType::ACHIEVEMENT,
                );
            }
        }

        return true;
    }
}
