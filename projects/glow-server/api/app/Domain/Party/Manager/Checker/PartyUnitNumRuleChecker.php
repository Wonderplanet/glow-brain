<?php

declare(strict_types=1);

namespace App\Domain\Party\Manager\Checker;

use App\Domain\Party\Enums\PartyRuleType;
use Illuminate\Support\Collection;

class PartyUnitNumRuleChecker implements IRuleChecker
{
    private int $ruleValue = 0;

    public function __construct(
        Collection $ruleValues
    ) {
        // 複数ある場合は、厳しいルールの方に合わせる
        $this->ruleValue = $ruleValues->map(function (string $ruleValue) {
            return intval($ruleValue);
        })->min();
    }

    public function checkRule(Collection $unitEntities): bool
    {
        return $unitEntities->count() <= $this->ruleValue;
    }

    public function getRuleInfo(): string
    {
        return sprintf(
            '%s [rule_value = %d]',
            PartyRuleType::PARTY_UNIT_NUM->value,
            $this->ruleValue
        );
    }
}
