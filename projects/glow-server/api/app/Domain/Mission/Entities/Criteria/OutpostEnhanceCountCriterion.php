<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * ゲートを X 回以上強化
 * criterion_type = OutpostEnhanceCount
 * criterion_value = null
 * criterion_count = X
 */
class OutpostEnhanceCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::OUTPOST_ENHANCE_COUNT;

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
