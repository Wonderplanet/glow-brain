<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定エンブレムXをYつ獲得しよう
 * criterion_type = SpecificEmblemAcquiredCount
 * criterion_value = mst_emblems.id(X)
 * criterion_count = Y
 */
class SpecificEmblemAcquiredCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_EMBLEM_ACQUIRED_COUNT;

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
