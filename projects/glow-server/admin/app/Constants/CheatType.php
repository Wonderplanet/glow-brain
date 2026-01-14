<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Cheat\Enums\CheatType as ApiCheatType;
use Illuminate\Support\Collection;

enum CheatType: string
{
    case BATTLE_TIME = ApiCheatType::BATTLE_TIME->value;
    case MAX_DAMAGE = ApiCheatType::MAX_DAMAGE->value;
    case BATTLE_STATUS_MISMATCH = ApiCheatType::BATTLE_STATUS_MISMATCH->value;
    case MASTER_DATA_STATUS_MISMATCH = ApiCheatType::MASTER_DATA_STATUS_MISMATCH->value;

    public function label(): string
    {
        return match ($this) {
            self::BATTLE_TIME => 'バトル時間(秒数が短い)',
            self::MAX_DAMAGE => '1発の最大ダメージ値',
            self::BATTLE_STATUS_MISMATCH => 'バトル前後のステータス不一致',
            self::MASTER_DATA_STATUS_MISMATCH => 'マスターデータとのステータス不一致',
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
