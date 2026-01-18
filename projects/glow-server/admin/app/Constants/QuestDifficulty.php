<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Stage\Enums\QuestDifficulty as BaseQuestDifficulty;
use Illuminate\Support\Collection;

enum QuestDifficulty: string
{
    case NORMAL = BaseQuestDifficulty::NORMAL->value;
    case HARD = BaseQuestDifficulty::HARD->value;
    case EXTRA = BaseQuestDifficulty::EXTRA->value;

    public function label(): string
    {
        return match ($this) {
            self::NORMAL => 'ノーマル',
            self::HARD => 'ハード',
            self::EXTRA => 'エクストラ',
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
