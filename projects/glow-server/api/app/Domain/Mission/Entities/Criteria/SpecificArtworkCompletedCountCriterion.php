<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定原画XをYつ完成させよう
 * criterion_type = SpecificSeriesArtworkCompletedCount
 * criterion_value = mst_artworks.id(X)
 * criterion_count = Y (原画完成は生涯1回のみなので実質Y=1で固定)
 */
class SpecificArtworkCompletedCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_ARTWORK_COMPLETED_COUNT;

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
