<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 公式X（エックス）をフォローする
 * criterion_type = FollowCompleted
 * criterion_value = null
 * criterion_count = 1（実際には無視されAPI側で1として認識する）
 */
class FollowCompletedCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::FOLLOW_COMPLETED;

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
