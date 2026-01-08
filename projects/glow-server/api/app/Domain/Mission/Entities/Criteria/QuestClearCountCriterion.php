<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 通算クエストクリア回数が X 回に到達
 * criterion_type = QuestClearCount
 * criterion_value = null
 * criterion_count = X
 */
class QuestClearCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::QUEST_CLEAR_COUNT;

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
