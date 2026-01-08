<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Shop\Enums\ShopType as ApiShopType;
use Illuminate\Support\Collection;

enum ShopType: string
{
    case COIN = ApiShopType::COIN->value;
    case DAILY = ApiShopType::DAILY->value;
    case WEEKLY = ApiShopType::WEEKLY->value;

    public function label(): string
    {
        return match ($this) {
            self::COIN => 'コイン',
            self::DAILY => 'デイリー',
            self::WEEKLY => 'ウィークリー',
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
