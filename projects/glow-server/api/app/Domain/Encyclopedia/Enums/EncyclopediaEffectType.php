<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Enums;

enum EncyclopediaEffectType: string
{
    // 体力
    case HP = 'Hp';
    // 攻撃力
    case ATTACK_POWER = 'AttackPower';
    // 回復
    case HEAL = 'Heal';
    // 召喚コスト
    case SUMMON_COST = 'SummonCost';
}
