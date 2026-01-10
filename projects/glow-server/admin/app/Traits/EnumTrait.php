<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Collection;

trait EnumTrait
{
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
