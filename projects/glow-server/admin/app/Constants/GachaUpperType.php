<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Gacha\Enums\UpperType as BaseGachaUpperType;
use Illuminate\Support\Collection;

enum GachaUpperType: string
{
    case MAX_RARITY = BaseGachaUpperType::MAX_RARITY->value;
    case PICKUP = BaseGachaUpperType::PICKUP->value;

    public function label(): string
    {
        return match ($this) {
            self::MAX_RARITY => '最高レア天井',
            self::PICKUP => 'ピックアップ天井',
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
