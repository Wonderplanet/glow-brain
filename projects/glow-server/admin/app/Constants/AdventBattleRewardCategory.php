<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory as ApiAdventBattleRewardCategory;

enum AdventBattleRewardCategory: string
{
    case MAX_SCORE = ApiAdventBattleRewardCategory::MAX_SCORE->value;
    case RANKING = ApiAdventBattleRewardCategory::RANKING->value;
    case RANK = ApiAdventBattleRewardCategory::RANK->value;
    case RAID_TOTAL_SCORE = ApiAdventBattleRewardCategory::RAID_TOTAL_SCORE->value;
}
