<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * アカウント連携を行う
 * criterion_type = AccountCompleted
 * criterion_value = null
 * criterion_count = 1（実際には無視されAPI側で1として認識する）
 */
class AccountCompletedCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ACCOUNT_COMPLETED;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::BINARY;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            1, // 1回クリアしていれば達成なので、mstCountは無視する
        );
    }
}
