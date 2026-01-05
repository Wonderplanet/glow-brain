<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * ユニットをY体獲得しよう
 * criterion_type = UnitAcquiredCount
 * criterion_value = NULL
 * criterion_count = Y
 */
class UnitAcquiredCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::UNIT_ACQUIRED_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
