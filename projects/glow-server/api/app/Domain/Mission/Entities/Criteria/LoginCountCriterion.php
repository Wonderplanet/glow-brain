<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 通算ログインが X 日に到達
 * criterion_type = LoginCount
 * criterion_value = null
 * criterion_count = X
 */
class LoginCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::LOGIN_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

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
