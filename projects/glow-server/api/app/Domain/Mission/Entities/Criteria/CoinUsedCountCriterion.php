<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * コインを X 枚使用した
 * criterion_type = CoinUsedCount
 * criterion_value = null
 * criterion_count = X
 */
class CoinUsedCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::COIN_USED_COUNT;

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
