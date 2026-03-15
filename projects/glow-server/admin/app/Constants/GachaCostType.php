<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Gacha\Enums\CostType as ApiGachaCostType;
use Illuminate\Support\Collection;

enum GachaCostType: string
{
    // 一次通貨(無償→有償の順で消費)
    case DIAMOND = ApiGachaCostType::DIAMOND->value;
    // 有償一次通貨
    case PAID_DIAMOND = ApiGachaCostType::PAID_DIAMOND->value;
    // 無料
    case FREE = ApiGachaCostType::FREE->value;
    // アイテム
    case ITEM = ApiGachaCostType::ITEM->value;
    // 広告
    case AD = ApiGachaCostType::AD->value;

    public function label(): string
    {
        return match ($this) {
            self::DIAMOND => 'プリズム',
            self::PAID_DIAMOND => '有償プリズム',
            self::FREE => '無料',
            self::ITEM => 'アイテム',
            self::AD => '広告視聴',
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