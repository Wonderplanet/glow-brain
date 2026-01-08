<?php

declare(strict_types=1);

namespace App\Constants;
use Illuminate\Support\Collection;

enum LogLoginType: int
{
    case FIRST_LOGIN = 1;
    case ADDITIIONAL_LOGIN = 0;

    public function label(): string
    {
        return match ($this) {
            self::FIRST_LOGIN => '初ログイン',
            self::ADDITIIONAL_LOGIN => 'ログイン2回目以降',
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
