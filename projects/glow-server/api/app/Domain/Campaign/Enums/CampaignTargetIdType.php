<?php

declare(strict_types=1);

namespace App\Domain\Campaign\Enums;

enum CampaignTargetIdType: string
{
    // mst_quest_idを絞り込みの条件とする
    case QUEST = 'Quest';
    // mst_series_idを絞り込みの条件とする
    case SERIES = 'Series';
}
