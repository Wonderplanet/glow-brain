<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Unit\Models\UsrUnitInterface;
use Illuminate\Support\Collection;

class UnitMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    public function sendLevelUpTrigger(
        UsrUnitInterface $usrUnit,
        int $beforeLevel,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::UNIT_LEVEL_UP_COUNT->value,
                null,
                max(0, $usrUnit->getLevel() - $beforeLevel),
            )
        );

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::UNIT_LEVEL->value,
                null,
                $usrUnit->getLevel(),
            )
        );

        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_UNIT_LEVEL->value,
                $usrUnit->getMstUnitId(),
                $usrUnit->getLevel(),
            )
        );
    }

    /**
     * ユニット獲得系のミッションの内で、新規獲得ユニットのみを対象とした重複なしでカウントするミッションのトリガーを送信する
     *
     * @param Collection<\App\Domain\Resource\Entities\Unit> $units
     */
    public function sendNewUnitTrigger(
        Collection $units,
    ): void {
        foreach ($units as $unit) {
            $usrUnit = $unit->getUsrUnit();
            $mstUnit = $unit->getMstUnit();

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::UNIT_LEVEL->value,
                    null,
                    $usrUnit->getLevel(),
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_LEVEL->value,
                    $usrUnit->getMstUnitId(),
                    $usrUnit->getLevel(),
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::UNIT_ACQUIRED_COUNT->value,
                    null,
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_SERIES_UNIT_ACQUIRED_COUNT->value,
                    $mstUnit->getMstSeriesId(),
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT->value,
                    $usrUnit->getMstUnitId(),
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT->value,
                    $usrUnit->getMstUnitId(),
                    $usrUnit->getRank(),
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT->value,
                    $usrUnit->getMstUnitId(),
                    $usrUnit->getGradeLevel(),
                )
            );
        }
    }

    /**
     * ユニット獲得系のミッションの内で、所持済みユニットだが重複ありでカウントするミッションのトリガーを送信する
     *
     * @param Collection $duplicatedMstUnitIds
     */
    public function sendDuplicatedUnitTrigger(
        Collection $duplicatedMstUnitIds,
    ): void {
        $mstUnitIdCountMap = $duplicatedMstUnitIds->countBy();

        foreach ($mstUnitIdCountMap as $mstUnitId => $count) {
            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_UNIT_ACQUIRED_COUNT->value,
                    $mstUnitId,
                    $count,
                )
            );
        }
    }

    public function sendRankUpTrigger(
        UsrUnitInterface $usrUnit,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_UNIT_RANK_UP_COUNT->value,
                $usrUnit->getMstUnitId(),
                $usrUnit->getRank(),
            )
        );
    }

    public function sendGradeUpTrigger(
        UsrUnitInterface $usrUnit,
    ): void {
        $this->missionDelegator->addTrigger(
            new MissionTrigger(
                MissionCriterionType::SPECIFIC_UNIT_GRADE_UP_COUNT->value,
                $usrUnit->getMstUnitId(),
                $usrUnit->getGradeLevel(),
            )
        );
    }
}
