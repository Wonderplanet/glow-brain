<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum IgnDisplayType: string
{
    // 簡易バナー
    case BASIC_BANNER = 'BasicBanner';

    // ダイアログ
    case DIALOG = 'Dialog';

    public function label(): string
    {
        return match ($this) {
            self::BASIC_BANNER => '簡易バナー',
            self::DIALOG => 'ダイアログ',
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
