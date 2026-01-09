<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Message\Enums\MngMessageType as BaseMngMessageType;
use Illuminate\Support\Collection;

enum MngMessageType: string
{
    case ALL = BaseMngMessageType::ALL->value;
    case INDIVIDUAL = BaseMngMessageType::INDIVIDUAL->value;

    public function label(): string
    {
        return match ($this) {
            self::ALL => '全体配布',
            self::INDIVIDUAL => '個別配布',
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
