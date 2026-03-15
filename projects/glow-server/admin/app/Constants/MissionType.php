<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionType as ApiMissionType;

enum MissionType: string
{
    case ACHIEVEMENT = ApiMissionType::ACHIEVEMENT->value;
    case BEGINNER = ApiMissionType::BEGINNER->value;
    case DAILY = ApiMissionType::DAILY->value;
    case WEEKLY = ApiMissionType::WEEKLY->value;
    case DAILY_BONUS = ApiMissionType::DAILY_BONUS->value;
    case EVENT = ApiMissionType::EVENT->value;
    case EVENT_DAILY = ApiMissionType::EVENT_DAILY->value;
    case EVENT_DAILY_BONUS = ApiMissionType::EVENT_DAILY_BONUS->value;
    case LIMITED_TERM = ApiMissionType::LIMITED_TERM->value;

    public function getIntValue(): int
    {
        return match ($this) {
            self::ACHIEVEMENT => 1,
            self::BEGINNER => 2,
            self::DAILY => 3,
            self::WEEKLY => 5,
            self::EVENT => ApiMissionType::EVENT->getIntValue(),
            self::EVENT_DAILY => ApiMissionType::EVENT_DAILY->getIntValue(),
            self::EVENT_DAILY_BONUS => ApiMissionType::EVENT_DAILY_BONUS->getIntValue(),
            self::LIMITED_TERM => ApiMissionType::LIMITED_TERM->getIntValue(),
        };
    }

    public static function getFromInt(int $value): self
    {
        return match ($value) {
            1 => self::ACHIEVEMENT,
            2 => self::BEGINNER,
            3 => self::DAILY,
            5 => self::WEEKLY,
            ApiMissionType::EVENT->getIntValue() => self::EVENT,
            ApiMissionType::EVENT_DAILY->getIntValue() => self::EVENT_DAILY,
            ApiMissionType::EVENT_DAILY_BONUS->getIntValue() => self::EVENT_DAILY_BONUS,
            ApiMissionType::LIMITED_TERM->getIntValue() => self::LIMITED_TERM,
        };
    }
}
