<?php

declare(strict_types=1);

namespace App\Domain\Mission\Utils;

use App\Domain\Common\Utils\StringUtil;
use App\Domain\Mission\Constants\MissionConstant;
use App\Domain\Mission\Enums\MissionCriterionType;

class MissionUtil
{
    public static function getCriterionTypeEnum(string $criterionType): ?MissionCriterionType
    {
        return MissionCriterionType::tryFrom($criterionType);
    }

    public static function isValidCriterionType(string $type): bool
    {
        if ($type === MissionCriterionType::NONE->value) {
            return false;
        }

        return MissionUtil::getCriterionTypeEnum($type) !== null;
    }

    public static function makeCriterionKey(
        string $criterionType,
        ?string $criterionValue,
    ): string {
        $criterionValue = StringUtil::isNotSpecified($criterionValue) ? '' : $criterionValue;

        $criterionKey = $criterionType;
        $criterionKey .= MissionConstant::CRITERION_KEY_DELIMITER . $criterionValue;
        return $criterionKey;
    }

    public static function isCompositeMissionCriterionType(string $criterionType): bool
    {
        return in_array(
            $criterionType,
            MissionConstant::COMPOSITE_MISSION_CRITERION_TYPES,
            true,
        );
    }

    public static function makeSpecificUnitStageClearCountCriterionValue(
        string $mstUnitId,
        string $mstStageId,
    ): string {
        return $mstUnitId . MissionConstant::CRITERION_VALUE_DELIMITER . $mstStageId;
    }
}
