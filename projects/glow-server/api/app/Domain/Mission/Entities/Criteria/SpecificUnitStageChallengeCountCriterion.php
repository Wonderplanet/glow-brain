<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定したユニットを編成して指定したステージに X 回挑戦
 * criterion_type = SpecificUnitStageChallengeCount
 * criterion_value = <mst_units.id>.<mst_stages.id>（2つのID文字列を連結した文字列）
 * criterion_count = X
 */
class SpecificUnitStageChallengeCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_UNIT_STAGE_CHALLENGE_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForTypeAndValue(
            $mstValue,
            $mstCount,
        );
    }
}
