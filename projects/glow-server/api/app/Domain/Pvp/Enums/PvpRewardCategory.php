<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Enums;

enum PvpRewardCategory: string
{
    // 順位報酬(開催期間報酬)
    case RANKING = 'Ranking';
    // ランク報酬(開催期間報酬)
    case RANK_ClASS = 'RankClass';
    // 累計ポイント報酬(開催期間報酬)
    case TOTAL_SCORE = 'TotalScore';
}
