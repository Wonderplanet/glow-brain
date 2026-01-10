<?php

declare(strict_types=1);

namespace App\Constants;

use App\Domain\Party\Enums\PartyRuleType as ApiPartyRuleType;

enum PartyRuleType: string
{
    case PARTY_UNIT_NUM                = ApiPartyRuleType::PARTY_UNIT_NUM->value;
    case PARTY_RARITY                  = ApiPartyRuleType::PARTY_RARITY->value;
    case PARTY_SERIES                  = ApiPartyRuleType::PARTY_SERIES->value;
    case PARTY_ATTACK_RANGE_TYPE       = ApiPartyRuleType::PARTY_ATTACK_RANGE_TYPE->value;
    case PARTY_ROLE_TYPE               = ApiPartyRuleType::PARTY_ROLE_TYPE->value;
    case PARTY_SUMMON_COST_UPPER_EQUAL = ApiPartyRuleType::PARTY_SUMMON_COST_UPPER_EQUAL->value;
    case PARTY_SUMMON_COST_LOWER_EQUAL = ApiPartyRuleType::PARTY_SUMMON_COST_LOWER_EQUAL->value;

    public function label(): string
    {
        return match ($this) {
            self::PARTY_UNIT_NUM                => '編成できるキャラ数が[ルール条件値]以下',
            self::PARTY_RARITY                  => 'レアリティ[ルール条件値]が編成できる',
            self::PARTY_SERIES                  => '[ルール条件値]の作品キャラが編成できる',
            self::PARTY_ATTACK_RANGE_TYPE       => '[ルール条件値]の射程キャラが編成できる',
            self::PARTY_ROLE_TYPE               => '[ルール条件値]のロールキャラが編成できる',
            self::PARTY_SUMMON_COST_UPPER_EQUAL => 'リーダーPが[ルール条件値]以下のキャラが編成できる',
            self::PARTY_SUMMON_COST_LOWER_EQUAL => 'リーダーPが[ルール条件値]以上のキャラが編成できる',
        };
    }
}
