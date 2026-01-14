<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

/**
 * 表示頻度の種別
 */
enum DisplayFrequencyType: string
{
    // 毎ログイン表示する
    case ALWAYS = 'Always';

    // 日ごとに表示する
    case DAILY = 'Daily';

    // 週ごとに表示する
    case WEEKLY = 'Weekly';

    // 月ごとに表示する
    case MONTHLY = 'Monthly';

    // 生涯一度だけ表示する
    case ONCE = 'Once';

    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => '毎ログイン',
            self::DAILY => 'デイリー',
            self::WEEKLY => 'ウィークリー',
            self::MONTHLY => 'マンスリー',
            self::ONCE => '一度きり',
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
