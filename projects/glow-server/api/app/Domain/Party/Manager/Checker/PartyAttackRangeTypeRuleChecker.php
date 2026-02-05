<?php

declare(strict_types=1);

namespace App\Domain\Party\Manager\Checker;

use App\Domain\Party\Enums\PartyRuleType;
use Illuminate\Support\Collection;

class PartyAttackRangeTypeRuleChecker implements IRuleChecker
{
    private Collection $ruleValues;

    public function __construct(
        Collection $ruleValues
    ) {
        $this->ruleValues = $ruleValues;
    }

    public function checkRule(Collection $unitEntities): bool
    {
        $violationMstUnits = $unitEntities->filter(function ($unitEntity) {
            /** @var \App\Domain\Resource\Entities\Unit $unitEntity */
            return !$this->ruleValues->contains($unitEntity->getMstUnit()->getAttackRangeType());
        });
        return $violationMstUnits->isEmpty();
    }

    public function getRuleInfo(): string
    {
        return sprintf(
            '%s [rule_value = %s]',
            PartyRuleType::PARTY_ATTACK_RANGE_TYPE->value,
            $this->ruleValues->implode(', ')
        );
    }
}
