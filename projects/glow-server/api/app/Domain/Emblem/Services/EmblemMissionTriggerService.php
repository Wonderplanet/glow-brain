<?php

declare(strict_types=1);

namespace App\Domain\Emblem\Services;

use App\Domain\Common\Entities\MissionTrigger;
use App\Domain\Mission\Delegators\MissionDelegator;
use App\Domain\Mission\Enums\MissionCriterionType;
use Illuminate\Support\Collection;

class EmblemMissionTriggerService
{
    public function __construct(
        private MissionDelegator $missionDelegator,
    ) {
    }

    /**
     * エンブレム獲得系のミッションの内で、新規獲得のみを対象とした重複なしでカウントするミッションのトリガーを送信する
     *
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstEmblemEntity> $mstEmblems
     */
    public function sendNewEmblemTrigger(
        Collection $mstEmblems,
    ): void {
        foreach ($mstEmblems as $mstEmblem) {
            $mstEmblemId = $mstEmblem->getId();
            $mstSeriesId = $mstEmblem->getMstSeriesId();

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::EMBLEM_ACQUIRED_COUNT->value,
                    null,
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_SERIES_EMBLEM_ACQUIRED_COUNT->value,
                    $mstSeriesId,
                    1,
                )
            );

            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT->value,
                    $mstEmblemId,
                    1,
                )
            );
        }
    }

    /**
     * エンブレム獲得系のミッションの内で、獲得済みだが重複ありでカウントするミッションのトリガーを送信する
     *
     * @param Collection $duplicatedMstEmblemIds
     */
    public function sendDuplicatedEmblemTrigger(
        Collection $duplicatedMstEmblemIds,
    ): void {
        $mstEmblemIdCountMap = $duplicatedMstEmblemIds->countBy();

        foreach ($mstEmblemIdCountMap as $mstEmblemId => $count) {
            $this->missionDelegator->addTrigger(
                new MissionTrigger(
                    MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT->value,
                    $mstEmblemId,
                    $count,
                )
            );
        }
    }
}
