<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Resource\Enums\RewardType;
use Illuminate\Support\Collection;

enum ShopItemResourceType: string
{
    // 無償一次通貨
    case FREE_DIAMOND = RewardType::FREE_DIAMOND->value;
    // 二次通貨
    case COIN = RewardType::COIN->value;
    // 探索連動二次通貨
    case IDLE_COIN = 'IdleCoin';
    // アイテム
    case ITEM = RewardType::ITEM->value;

    public function label(): string
    {
        return match ($this) {
            self::FREE_DIAMOND => '無償プリズム',
            self::COIN => 'コイン',
            self::IDLE_COIN => '探索連動コイン',
            self::ITEM => 'アイテム',
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
