<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 原画をYつ完成させよう
 * criterion_type = ArtworkCompletedCount
 * criterion_value = NULL
 * criterion_count = Y
 */
class ArtworkCompletedCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ARTWORK_COMPLETED_COUNT;

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
