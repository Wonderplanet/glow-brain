<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Stage\Enums\QuestType as ApiQuestType;
use Illuminate\Support\Collection;

enum QuestType: string
{
    case NORMAL = ApiQuestType::NORMAL->value;
    case EVENT = ApiQuestType::EVENT->value;
    case ENHANCE = ApiQuestType::ENHANCE->value;

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'メインクエスト',
            self::EVENT => 'イベントクエスト',
            self::ENHANCE => 'コイン獲得クエスト',
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
