<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;

/**
 * PVPに X 回勝利する
 * criterion_type = PvpWinCount
 * criterion_value = null
 * criterion_count = X
 */
class PvpWinCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::PVP_WIN_COUNT;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
