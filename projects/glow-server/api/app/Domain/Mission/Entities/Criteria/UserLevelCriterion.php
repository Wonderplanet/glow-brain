<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * プレイヤーランクが X 到達
 * criterion_type = UserLevel
 * criterion_value = null
 * criterion_count = X
 */
class UserLevelCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::USER_LEVEL;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::MAX;

    protected array $conditionTypes = [
        MissionCriterionConditionType::CLEAR,
        MissionCriterionConditionType::UNLOCK,
    ];

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
