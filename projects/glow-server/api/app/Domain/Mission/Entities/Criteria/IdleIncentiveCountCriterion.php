<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 探索を X 回行う
 * criterion_type = IdleIncentiveCount
 * criterion_value = null
 * criterion_count = X
 */
class IdleIncentiveCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::IDLE_INCENTIVE_COUNT;

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
