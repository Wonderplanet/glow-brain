<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 広告動画を X 回再生した
 * criterion_type = IaaCount
 * criterion_value = null
 * criterion_count = X
 */
class IaaCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::IAA_COUNT;

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
