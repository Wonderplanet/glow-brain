<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Common\Enums\UserStatus as ApiUserStatus;
use Illuminate\Support\Collection;

enum UserStatus: int
{
    case NORMAL = ApiUserStatus::NORMAL->value;
    case REFUNDING = ApiUserStatus::REFUNDING->value;
    case BAN_TEMPORARY_CHEATING = ApiUserStatus::BAN_TEMPORARY_CHEATING->value;
    case BAN_TEMPORARY_DETECTED_ANOMALY = ApiUserStatus::BAN_TEMPORARY_DETECTED_ANOMALY->value;
    case BAN_PERMANENT = ApiUserStatus::BAN_PERMANENT->value;
    case DELETED = ApiUserStatus::DELETED->value;

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => '通常',
            self::REFUNDING => '返金対応中',
            self::BAN_TEMPORARY_CHEATING => '時限BAN(不正行為)',
            self::BAN_TEMPORARY_DETECTED_ANOMALY => '時限BAN(異常データ検出)',
            self::BAN_PERMANENT => '永久BAN',
            self::DELETED => '削除',
        };
    }

    public function color(bool $isColorCode = false): string
    {
        if ($isColorCode) {
            return match ($this) {
                self::NORMAL => '#22c55e',
                self::REFUNDING => '#06b6d4',
                self::BAN_TEMPORARY_CHEATING,
                self::BAN_TEMPORARY_DETECTED_ANOMALY => '#f59e42',
                self::BAN_PERMANENT => '#ef4444',
                self::DELETED => '#64748b',
            };
        }

        return match ($this) {
            self::NORMAL => 'success',
            self::REFUNDING => 'info',
            self::BAN_TEMPORARY_CHEATING,
            self::BAN_TEMPORARY_DETECTED_ANOMALY => 'warning',
            self::BAN_PERMANENT => 'danger',
            self::DELETED => 'secondary',
        };
    }

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->label());
        }
        return $labels;
    }

    public static function getSuspensionReasons(): array
    {
        $cases = [
            self::REFUNDING,
            self::BAN_TEMPORARY_CHEATING,
            self::BAN_TEMPORARY_DETECTED_ANOMALY,
        ];

        $reasons = [];
        foreach ($cases as $case) {
            $reasons[$case->value] = $case->label();
        }
        return $reasons;
    }

    /**
     * 一時的な停止中かどうか
     */
    public static function isTemporarySuspended(?int $status): bool
    {
        if ($status === null) {
            return false;
        }

        $enum = self::tryFrom($status);
        if ($enum === null) {
            return false;
        }

        return match ($enum) {
            self::REFUNDING,
            self::BAN_TEMPORARY_CHEATING,
            self::BAN_TEMPORARY_DETECTED_ANOMALY => true,
            default => false,
        };
    }

    /**
     * 時限BANのステータスかどうか
     */
    public static function isBanTemporaryStatus(?int $status): bool
    {
        if ($status === null) {
            return false;
        }

        $enum = self::tryFrom($status);
        if ($enum === null) {
            return false;
        }

        return match ($enum) {
            self::BAN_TEMPORARY_CHEATING,
            self::BAN_TEMPORARY_DETECTED_ANOMALY => true,
            default => false,
        };
    }

    /**
     * 時限BANの期間の選択肢
     * @return int[]
     */
    public static function getBanTemporarySuspendPeriodDayOptions(): array
    {
        $dayOptions = [3, 5, 7];

        $options = [];
        foreach ($dayOptions as $day) {
            $options[$day] = $day . '日';
        }
        return $options;
    }
}
