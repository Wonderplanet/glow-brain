<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * 強敵キャラ X 体撃破
 * criterion_type = DefeatBossEnemyCount
 * criterion_value = null
 * criterion_count = X
 */
class DefeatBossEnemyCountCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::DEFEAT_BOSS_ENEMY_COUNT;

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
