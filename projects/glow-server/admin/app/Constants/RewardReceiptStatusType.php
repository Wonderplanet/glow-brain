<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum RewardReceiptStatusType: int
{
    case NOT_RECEIVED = 0;
    case RECEIVED = 1;

    public function label(): string
    {
        return match ($this) {
            self::NOT_RECEIVED => '未受取',
            self::RECEIVED => '受取済',
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
