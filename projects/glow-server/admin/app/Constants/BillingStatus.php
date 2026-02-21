<?php

namespace App\Constants;

use Illuminate\Support\Collection;

enum BillingStatus: string
{
    case CHARGES_APPLY = 'ChargesApply';
    case NO_CHARGE = 'NoCharge';
    case NO_CONDITIONS = 'NoConditions';

    public function label(): string
    {
        return match ($this) {
            self::CHARGES_APPLY => '課金あり',
            self::NO_CHARGE => '課金なし',
            self::NO_CONDITIONS => '条件なし',

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
