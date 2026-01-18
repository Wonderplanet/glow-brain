<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionStatus as ApiMissionStatus;
use Illuminate\Support\Collection;

enum MissionStatus: int
{
    // api側の定義
    case UNCLEAR = ApiMissionStatus::UNCLEAR->value;
    case CLEAR = ApiMissionStatus::CLEAR->value;
    case RECEIVED_REWARD = ApiMissionStatus::RECEIVED_REWARD->value;

    // admin側でのみ使用する定義
    case LOCKED = 100;

    public function label(): string
    {
        return match ($this) {
            self::UNCLEAR => '未達成',
            self::CLEAR => '達成済',
            self::RECEIVED_REWARD => '報酬受取済',
            self::LOCKED => '未開放',
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

    public function badge(): string
    {
        return match ($this) {
            self::LOCKED => 'gray',
            self::UNCLEAR => 'primary',
            self::CLEAR => 'info',
            self::RECEIVED_REWARD => 'success',
        };
    }
}
