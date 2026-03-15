<?php

declare(strict_types=1);

namespace App\Constants;
use Illuminate\Support\Collection;
use App\Domain\Resource\Log\Enums\LogResourceActionType as ApiLogResourceActionType;

enum LogResourceActionType: string
{
    case GET = ApiLogResourceActionType::GET->value;
    case USE = ApiLogResourceActionType::USE->value;

    public function label(): string
    {
        return match ($this) {
            self::GET => '獲得',
            self::USE => '消費',
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
