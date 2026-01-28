<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Emblem\Enums\EmblemType as ApiEmblemType;
use Illuminate\Support\Collection;

enum EmblemType: string
{
    case SERIES = ApiEmblemType::SERIES->value;
    case EVENT  = ApiEmblemType::EVENT->value;

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