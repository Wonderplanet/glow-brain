<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * エンブレムをYつ獲得しよう
 * criterion_type = EmblemAcquiredCount
 * criterion_value = NULL
 * criterion_count = Y
 */
class EmblemAcquiredCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::EMBLEM_ACQUIRED_COUNT;

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
