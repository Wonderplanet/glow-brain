<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Shop\Enums\ProductType as ApiProductType;
use Illuminate\Support\Collection;

enum ProductType: string
{
    // ダイヤモンド
    case DIAMOND = ApiProductType::DIAMOND->value;

    // パック
    case PACK = ApiProductType::PACK->value;
    // パス
    case PASS = ApiProductType::PASS->value;

    public function label(): string
    {
        return match ($this) {
            self::DIAMOND => 'プリズム',
            self::PACK => 'パック',
            self::PASS => 'パス',
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
