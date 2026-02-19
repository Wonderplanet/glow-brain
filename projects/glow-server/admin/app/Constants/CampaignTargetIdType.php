<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Campaign\Enums\CampaignTargetIdType as ApiCampaignTargetIdType;
use Illuminate\Support\Collection;

enum CampaignTargetIdType: string
{
    case QUEST = ApiCampaignTargetIdType::QUEST->value;
    case SERIES = ApiCampaignTargetIdType::SERIES->value;

    public function label(): string
    {
        return match ($this) {
            self::QUEST => 'クエスト',
            self::SERIES => '作品',
        };
    }

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
