<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Enums;

enum CampaignTargetType: string
{
    // ノーマルクエスト
    case NORMAL_QUEST = 'NormalQuest';
    // 強化クエスト
    case ENHANCE_QUEST = 'EnhanceQuest';
    // イベントクエスト
    case EVENT_QUEST = 'EventQuest';
    // PVP
    case PVP = 'PvP';
    // 降臨バトル
    case ADVENT_BATTLE = 'AdventBattle';
}
