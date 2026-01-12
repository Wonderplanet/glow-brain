<?php

declare(strict_types=1);

namespace App\Domain\InGame\Enums;

enum InGameSpecialRuleType: string
{
    /**
     * ゲート関連
     */

    // ヒーローゲートのHPがNで開始
    case OUTPOST_HP = 'OutpostHp';

    /**
     * パーティ関連
     */
    case PARTY_UNIT_NUM = 'PartyUnitNum';
    case PARTY_RARITY = 'PartyRarity';
    case PARTY_SERIES = 'PartySeries';
    case PARTY_ATTACK_RANGE_TYPE = 'PartyAttackRangeType';
    case PARTY_ROLE_TYPE = 'PartyRoleType';
    case PARTY_SUMMON_COST_UPPER_EQUAL = 'PartySummonCostUpperEqual';
    case PARTY_SUMMON_COST_LOWER_EQUAL = 'PartySummonCostLowerEqual';

    /**
     * インゲームコンテンツ関連
     */

    // コンティニュー不可
    case NO_CONTINUE = 'NoContinue';
    // スピードアタック
    case SPEED_ATTACK = 'SpeedAttack';
    // タイムリミット
    case TIME_LIMIT = 'TimeLimit';
}
