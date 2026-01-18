<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 連続ログインが X 日に到達
 * criterion_type = LoginContinueCount
 * criterion_value = null
 * criterion_count = X
 */
class LoginContinueCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::LOGIN_CONTINUE_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SYNC;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
