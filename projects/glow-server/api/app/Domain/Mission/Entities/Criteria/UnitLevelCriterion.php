<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

class UnitLevelCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::UNIT_LEVEL;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::MAX;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
