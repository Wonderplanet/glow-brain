<?php

declare(strict_types=1);

namespace App\Domain\Shop\Enums;

enum PassEffectType: string
{
    case IDLE_INCENTIVE_ADD_REWARD = 'IdleIncentiveAddReward';
    case IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_DIAMOND = 'IdleIncentiveMaxQuickReceiveByDiamond';
    case IDLE_INCENTIVE_MAX_QUICK_RECEIVE_BY_AD = 'IdleIncentiveMaxQuickReceiveByAd';
    case STAMINA_ADD_RECOVERY_LIMIT = 'StaminaAddRecoveryLimit';
    case AD_SKIP = 'AdSkip';
    case CHANGE_BATTLE_SPEED = 'ChangeBattleSpeed';
}
