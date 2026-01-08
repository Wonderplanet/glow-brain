<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 同じミッションタイプのミッションを X 件クリア
 * criterion_type = MissionClearCount
 * criterion_value = null
 * criterion_count = X
 */
class MissionClearCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::MISSION_CLEAR_COUNT;

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
