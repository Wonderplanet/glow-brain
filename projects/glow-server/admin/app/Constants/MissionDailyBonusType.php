<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionDailyBonusType as ApiMissionDailyBonusType;
use Illuminate\Support\Collection;

enum MissionDailyBonusType: string
{
    case DAILY_BONUS = ApiMissionDailyBonusType::DAILY_BONUS->value;

    public function label(): string
    {
        return match ($this) {
            self::DAILY_BONUS => '連続ログイン',
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
}
