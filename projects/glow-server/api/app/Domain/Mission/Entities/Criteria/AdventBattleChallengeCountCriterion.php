<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;

/**
 * 降臨バトルを X 回挑戦する
 * criterion_type = AdventBattleChallengeCount
 * criterion_value = null
 * criterion_count = X
 */
class AdventBattleChallengeCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ADVENT_BATTLE_CHALLENGE_COUNT;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
