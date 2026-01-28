<?php

declare(strict_types=1);

namespace App\Domain\Party\Enums;

enum PartyRuleType: string
{
    case PARTY_UNIT_NUM = 'PartyUnitNum';
    case PARTY_RARITY = 'PartyRarity';
    case PARTY_SERIES = 'PartySeries';
    case PARTY_ATTACK_RANGE_TYPE = 'PartyAttackRangeType';
    case PARTY_ROLE_TYPE = 'PartyRoleType';
    case PARTY_SUMMON_COST_UPPER_EQUAL = 'PartySummonCostUpperEqual';
    case PARTY_SUMMON_COST_LOWER_EQUAL = 'PartySummonCostLowerEqual';
}
