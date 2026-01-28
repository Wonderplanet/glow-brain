<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities\Criteria;

use App\Domain\Mission\Enums\MissionCriterionType;
use App\Domain\Mission\Enums\MissionProgressAggregationMethod;

/**
 * ミッション機能解放からの日数が X 日に到達
 * criterion_type = DaysFromUnlockedMission
 * criterion_value = null
 * criterion_count = X
 */
class DaysFromUnlockedMissionCriterion extends MissionCriterion
{
    protected MissionCriterionType $type = MissionCriterionType::DAYS_FROM_UNLOCKED_MISSION;

    protected MissionProgressAggregationMethod $progressAggregationMethod = MissionProgressAggregationMethod::MAX;

    /**
     * マスタデータ設定されないCriterionTypeなので、マスタデータ取得クエリで取得しないように、空配列を設定
     *
     * 初心者ミッションの開放にのみ使用する
     */
    protected array $conditionTypes = [];

    public function canClear(
        ?string $mstValue,
        int $mstCount,
    ): bool {
        return $this->isCountGteForType(
            $mstCount,
        );
    }
}
