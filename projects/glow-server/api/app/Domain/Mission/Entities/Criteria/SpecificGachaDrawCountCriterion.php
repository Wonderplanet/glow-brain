<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 指定ガシャXをY回引く
 * criterion_type = SpecificGachaDrawCount
 * criterion_value = opr_gachas.id
 * criterion_count = Y
 */
class SpecificGachaDrawCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_GACHA_DRAW_COUNT;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::SUM;

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForTypeAndValue(
            $mstValue,
            $mstCount,
        );
    }
}
