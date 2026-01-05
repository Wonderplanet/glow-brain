<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionConditionType;
use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定したクエストをクリアする
 * criterion_type = SpecificQuestClear
 * criterion_value = mst_quests.id
 * criterion_count = 1（実際には無視されAPI側で1として認識する）
 */
class SpecificQuestClearCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_QUEST_CLEAR;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    protected array $conditionTypes = [
        MissionCriterionConditionType::CLEAR,
        MissionCriterionConditionType::UNLOCK,
    ];

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForTypeAndValue(
            $mstValue,
            1, // 1回クリアしていれば達成なので、mstCountは無視する
        );
    }
}
