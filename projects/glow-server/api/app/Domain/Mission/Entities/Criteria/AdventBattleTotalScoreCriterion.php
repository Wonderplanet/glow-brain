<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;

/**
 * 降臨バトルの累計スコアが X 達成
 * criterion_type = AdventBattleTotalScore
 * criterion_value = null
 * criterion_count = X
 */
class AdventBattleTotalScoreCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ADVENT_BATTLE_TOTAL_SCORE;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
