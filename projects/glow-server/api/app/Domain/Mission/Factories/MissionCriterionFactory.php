<?php

declare(strict_types=1);

namespace App\Domain\Mission\Factories;

use App\Domain\Mission\Entities\Criteria\MissionBonusPointCriterion;
use App\Domain\Mission\Entities\Criteria\MissionCriterion;
use App\Domain\Mission\Utils\MissionUtil;

class MissionCriterionFactory
{
    public function __construct()
    {
    }

    public function createMissionCriterion(
        ?string $criterionType,
        ?string $criterionValue,
        ?int $progress = null,
    ): ?MissionCriterion {
        $criterionTypeEnum = MissionUtil::getCriterionTypeEnum($criterionType);
        if (is_null($criterionTypeEnum)) {
            return null;
        }
        $criterionClass = $criterionTypeEnum->getCriterionClass();
        if (is_null($criterionClass)) {
            return null;
        }

        return new $criterionClass(
            $criterionValue,
            $progress,
        );
    }

    public function createMissionBonusPointCriterion(
        ?int $progress = null,
    ): MissionCriterion {
        return new MissionBonusPointCriterion(
            null,
            $progress ?? 0,
        );
    }
}
