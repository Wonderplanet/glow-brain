<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 降臨バトルのハイスコアが X 達成
 * criterion_type = AdventBattleScore
 * criterion_value = null
 * criterion_count = X
 */
class AdventBattleScoreCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ADVENT_BATTLE_SCORE;

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
