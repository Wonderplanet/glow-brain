<?php

declare(strict_types=1);

namespace App\Constants;

// TODO api側にクラスが作成されたらそちらを参照する
use Illuminate\Support\Collection;

enum PvpBonusType: string
{
    case CLEAR_TIME = 'ClearTime';
    case WIN_OVER_BONUS = "WinOverBonus";
    case WIN_NORMAL_BONUS = "WinNormalBonus";
    case WIN_UNDER_BONUS = "WinUnderBonus";

    public static function labels(): Collection
    {
        $cases = self::cases();
        $labels = collect();
        foreach ($cases as $case) {
            $labels->put($case->value, $case->value);
        }
        return $labels;
    }
}
