<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Gacha\Enums\AppearanceCondition as BaseGachaAppearanceCondition;
use Illuminate\Support\Collection;

enum GachaAppearanceCondition: string
{
    case ALWAYS = BaseGachaAppearanceCondition::ALWAYS->value;
    case HAS_TICKET = BaseGachaAppearanceCondition::HAS_TICKET->value;

    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => '常に表示',
            self::HAS_TICKET => 'チケット所持中のみ表示',
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
