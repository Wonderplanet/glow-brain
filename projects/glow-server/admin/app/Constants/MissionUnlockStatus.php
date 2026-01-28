<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Mission\Enums\MissionUnlockStatus as ApiMissionUnlockStatus;
use Illuminate\Support\Collection;

enum MissionUnlockStatus: int
{
    // api側の定義
    case LOCK = ApiMissionUnlockStatus::LOCK->value;
    case OPEN = ApiMissionUnlockStatus::OPEN->value;

    public function label(): string
    {
        return match ($this) {
            self::LOCK => '未開放',
            self::OPEN => '開放済',
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
            self::LOCK => 'gray',
            self::OPEN => 'success',
        };
    }
}
