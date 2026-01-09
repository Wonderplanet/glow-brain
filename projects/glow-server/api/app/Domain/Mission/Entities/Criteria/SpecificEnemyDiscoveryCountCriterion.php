<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 敵キャラXをY体発見しよう
 * criterion_type = SpecificEnemyDiscoveryCount
 * criterion_value = mst_enemy_characters.id(X)
 * criterion_count = Y
 */
class SpecificEnemyDiscoveryCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::SPECIFIC_ENEMY_DISCOVERY_COUNT;

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
