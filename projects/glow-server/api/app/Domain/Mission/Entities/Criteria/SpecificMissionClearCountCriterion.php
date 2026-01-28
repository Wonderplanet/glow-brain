<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定したミッションをX個クリアする
 * criterion_valueで指定したグループキーを持つミッションが対象
 *
 * criterion_type = SpecificMissionClearCount
 * criterion_value = mst_mission_<ミッションタイプ>s.group_key
 * criterion_count = X
 */
class SpecificMissionClearCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_MISSION_CLEAR_COUNT;

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
