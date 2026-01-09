<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Enums;

enum AdventBattleRewardCategory: string
{
    // 最大スコア報酬
    case MAX_SCORE = 'MaxScore';
    // 順位報酬(開催期間報酬)
    case RANKING = 'Ranking';
    // ランク報酬(開催期間報酬)
    case RANK = 'Rank';
    // 協力バトル全ユーザー累計スコア報酬
    case RAID_TOTAL_SCORE = 'RaidTotalScore';
}
