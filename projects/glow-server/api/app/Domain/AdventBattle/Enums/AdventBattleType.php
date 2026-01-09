<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Enums;

enum AdventBattleType: string
{
    // スコアチャレンジ
    case SCORE_CHALLENGE = 'ScoreChallenge';
    // 協力バトル
    case RAID = 'Raid';
}
