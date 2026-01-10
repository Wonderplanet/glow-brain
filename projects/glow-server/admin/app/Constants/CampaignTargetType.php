<?php

declare(strict_types=1);

namespace App\Constants;

use Illuminate\Support\Collection;

enum CampaignTargetType: string
{
    case NORMAL_QUEST = 'NormalQuest';
    case ENHANCE_QUEST = 'EnhanceQuest';
    case EVENT_QUEST = 'EventQuest';
    case PVP = 'PvP';
    case ADVENT_BATTLE = 'AdventBattle';

    public function label(): string
    {
        return match ($this) {
            self::NORMAL_QUEST => 'ノーマルクエスト',
            self::ENHANCE_QUEST => '強化クエスト',
            self::EVENT_QUEST => 'イベントクエスト',
            self::PVP => 'ランクマッチ',
            self::ADVENT_BATTLE => '降臨バトル',
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
