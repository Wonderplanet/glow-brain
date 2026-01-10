<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Shop\Enums\ShopItemCostType as ApiShopItemCostType;
use Illuminate\Support\Collection;

enum ShopItemCostType: string
{
    // コイン(2次通貨)
    case COIN = ApiShopItemCostType::COIN->value;
    // 有償一次通貨
    case PAID_DIAMOND = ApiShopItemCostType::PAID_DIAMOND->value;
    // 一次通貨(無償→有償の順で消費)
    case DIAMOND = ApiShopItemCostType::DIAMOND->value;
    // 広告
    case AD = ApiShopItemCostType::AD->value;
    // 無料
    case FREE = ApiShopItemCostType::FREE->value;

    public function label(): string
    {
        return match ($this) {
            self::COIN => 'コイン',
            self::PAID_DIAMOND => '有償プリズム',
            self::DIAMOND => 'プリズム',
            self::AD => '広告視聴',
            self::FREE => '無料',
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
