<?php

declare(strict_types=1);

namespace App\Domain\Cheat\Enums;

enum CheatType: string
{
    // バトル時間(秒数が短い)
    case BATTLE_TIME = 'BattleTime';
    // 1発の最大ダメージ値
    case MAX_DAMAGE = 'MaxDamage';
    // バトル前後のステータス不一致
    case BATTLE_STATUS_MISMATCH = 'BattleStatusMismatch';
    // マスターデータとのステータス不一致
    case MASTER_DATA_STATUS_MISMATCH = 'MasterDataStatusMismatch';
}
