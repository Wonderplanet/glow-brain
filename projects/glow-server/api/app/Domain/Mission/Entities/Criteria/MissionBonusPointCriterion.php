<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 獲得済ミッションボーナスポイントが X 以上
 * criterion_type = MissionBonusPoint
 * criterion_value = null
 * criterion_count = X
 */
class MissionBonusPointCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::MISSION_BONUS_POINT;

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
