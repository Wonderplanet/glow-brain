<?php

declare(strict_types=1);

namespace App\Domain\Party\Manager\Checker;

use App\Domain\Party\Enums\PartyRuleType;
use Illuminate\Support\Collection;

class PartySummonCostLowerEqualRuleChecker implements IRuleChecker
{
    private int $ruleValue = 0;

    public function __construct(
        Collection $ruleValues
    ) {
        // 複数ある場合は、厳しいルールの方に合わせる
        $this->ruleValue = $ruleValues->map(function (string $ruleValue) {
            return intval($ruleValue);
        })->max();
    }

    public function checkRule(Collection $unitEntities): bool
    {
        $violationMstUnits = $unitEntities->filter(function ($unitEntity) {
            /** @var \App\Domain\Resource\Entities\Unit $unitEntity */
            return $this->ruleValue > $unitEntity->getMstUnit()->getSummonCost();
        });
        return $violationMstUnits->isEmpty();
    }

    public function getRuleInfo(): string
    {
        return sprintf(
            '%s [rule_value = %d]',
            PartyRuleType::PARTY_SUMMON_COST_LOWER_EQUAL->value,
            $this->ruleValue
        );
    }
}
