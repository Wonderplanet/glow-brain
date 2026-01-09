<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 敵キャラをY体発見しよう
 * criterion_type = EnemyDiscoveryCount
 * criterion_value = NULL
 * criterion_count = Y
 */
class EnemyDiscoveryCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::ENEMY_DISCOVERY_COUNT;

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
