<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定したゲート強化項目がLxXに到達する
 * criterion_type = SpecificOutpostEnhanceLevel
 * criterion_value = mst_outpost_enhancements.id
 * criterion_count = X
 */
class SpecificOutpostEnhanceLevelCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_OUTPOST_ENHANCE_LEVEL;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::MAX;

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
