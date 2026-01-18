<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Enums;

enum AdventBattleRankType: string
{
    // ブロンズ
    case BRONZE = 'Bronze';
    // シルバー
    case SILVER = 'Silver';
    // ゴールド
    case GOLD = 'Gold';
    // マスター
    case MASTER = 'Master';
}
