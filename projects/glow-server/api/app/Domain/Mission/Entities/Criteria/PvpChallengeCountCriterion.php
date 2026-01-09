<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;

/**
 * PVPを X 回挑戦する
 * criterion_type = PvpChallengeCount
 * criterion_value = null
 * criterion_count = X
 */
class PvpChallengeCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::PVP_CHALLENGE_COUNT;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
