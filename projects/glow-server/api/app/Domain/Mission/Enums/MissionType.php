<?php

declare(strict_types=1);

namespace App\Domain\Mission\Enums;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

enum MissionType: string
{
    case ACHIEVEMENT = 'Achievement';
    case BEGINNER = 'Beginner';
    case DAILY = 'Daily';
    case DAILY_BONUS = 'DailyBonus';
    case WEEKLY = 'Weekly';
    case EVENT = 'Event';
    case EVENT_DAILY = 'EventDaily';
    case EVENT_DAILY_BONUS = 'EventDailyBonus';
    case LIMITED_TERM = 'LimitedTerm';

    public function getIntValue(): int
    {
        return match ($this) {
            self::ACHIEVEMENT => 1,
            self::BEGINNER => 2,
            self::DAILY => 3,
            self::DAILY_BONUS => 4,
            self::WEEKLY => 5,
            self::EVENT => 11,
            self::EVENT_DAILY => 12,
            self::EVENT_DAILY_BONUS => 13,
            self::LIMITED_TERM => 21,
        };
    }

    public static function getFromInt(int $value): self
    {
        return match ($value) {
            1 => self::ACHIEVEMENT,
            2 => self::BEGINNER,
            3 => self::DAILY,
            4 => self::DAILY_BONUS,
            5 => self::WEEKLY,
            11 => self::EVENT,
            12 => self::EVENT_DAILY,
            13 => self::EVENT_DAILY_BONUS,
            21 => self::LIMITED_TERM,
            default => throw new GameException(ErrorCode::INVALID_PARAMETER, 'Invalid MissionType value: ' . $value),
        };
    }

    public static function getFromString(string $value): self
    {
        return match ($value) {
            self::ACHIEVEMENT->value => self::ACHIEVEMENT,
            self::BEGINNER->value => self::BEGINNER,
            self::DAILY->value => self::DAILY,
            self::DAILY_BONUS->value => self::DAILY_BONUS,
            self::WEEKLY->value => self::WEEKLY,
            self::EVENT->value => self::EVENT,
            self::EVENT_DAILY->value => self::EVENT_DAILY,
            self::EVENT_DAILY_BONUS->value => self::EVENT_DAILY_BONUS,
            self::LIMITED_TERM->value => self::LIMITED_TERM,
            default => throw new GameException(ErrorCode::INVALID_PARAMETER, 'Invalid MissionType value: ' . $value),
        };
    }

    /**
     * ノーマル系のミッションタイプをリストアップした配列を取得する
     * @return array<self>
     */
    public static function getNormals(): array
    {
        return [
            self::ACHIEVEMENT,
            self::DAILY,
            self::WEEKLY,
            self::BEGINNER,
        ];
    }

    /**
     * ノーマル系のミッションタイプかどうかを、enum値(string)から判定する
     * @param string $value
     * @return bool
     */
    public static function isNormalByString(string $value): bool
    {
        return in_array(self::getFromString($value), self::getNormals(), true);
    }

    public function isNormal(): bool
    {
        return in_array($this, self::getNormals(), true);
    }

    public function isBeginner(): bool
    {
        return $this === self::BEGINNER;
    }

    /**
     * イベント系のミッションタイプをリストアップした配列を取得する
     * @return array<self>
     */
    public static function getEvents(): array
    {
        return [
            self::EVENT,
            self::EVENT_DAILY,
        ];
    }

    public function isEvent(): bool
    {
        return in_array($this, self::getEvents(), true);
    }
}
