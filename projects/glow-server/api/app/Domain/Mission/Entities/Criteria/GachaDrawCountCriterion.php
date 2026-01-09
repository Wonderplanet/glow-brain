<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 通算でガチャをY回引く
 * criterion_type = GachaDrawCount
 * criterion_value = null
 * criterion_count = Y
 */
class GachaDrawCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::GACHA_DRAW_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
