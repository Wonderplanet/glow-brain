<?php

declare(strict_types=1);

namespace App\Domain\Party\Manager;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Party\Enums\PartyRuleType;
use App\Domain\Party\Manager\Checker\PartyAttackRangeTypeRuleChecker;
use App\Domain\Party\Manager\Checker\PartyRarityRuleChecker;
use App\Domain\Party\Manager\Checker\PartyRoleTypeRuleChecker;
use App\Domain\Party\Manager\Checker\PartySeriesRuleChecker;
use App\Domain\Party\Manager\Checker\PartySummonCostLowerEqualRuleChecker;
use App\Domain\Party\Manager\Checker\PartySummonCostUpperEqualRuleChecker;
use App\Domain\Party\Manager\Checker\PartyUnitNumRuleChecker;
use Illuminate\Support\Collection;

class RuleCheckManager
{
    /**
     * @var Collection<\App\Domain\Party\Manager\Checker\IRuleChecker>
     */
    private Collection $ruleCheckers;

    /**
     * ルールの追加
     *
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity> $mstInGameSpecialRules
     *
     * @return void
     * @throws \Throwable
     */
    public function setRules(Collection $mstInGameSpecialRules): void
    {
        $this->ruleCheckers = collect();
        $groupedMstInGameSpecialRules = $mstInGameSpecialRules->groupBy(function ($entity): string {
            return $entity->getRuleType();
        });
        foreach ($groupedMstInGameSpecialRules as $ruleType => $mstInGameSpecialRules) {
            $ruleValues = $mstInGameSpecialRules->map(function ($entity) {
                /** @var \App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity $entity */
                return $entity->getRuleValue();
            });
            switch ($ruleType) {
                case PartyRuleType::PARTY_UNIT_NUM->value:
                    $this->ruleCheckers->push(new PartyUnitNumRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_RARITY->value:
                    $this->ruleCheckers->push(new PartyRarityRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_SERIES->value:
                    $this->ruleCheckers->push(new PartySeriesRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_ATTACK_RANGE_TYPE->value:
                    $this->ruleCheckers->push(new PartyAttackRangeTypeRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_ROLE_TYPE->value:
                    $this->ruleCheckers->push(new PartyRoleTypeRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_SUMMON_COST_UPPER_EQUAL->value:
                    $this->ruleCheckers->push(new PartySummonCostUpperEqualRuleChecker($ruleValues));
                    break;
                case PartyRuleType::PARTY_SUMMON_COST_LOWER_EQUAL->value:
                    $this->ruleCheckers->push(new PartySummonCostLowerEqualRuleChecker($ruleValues));
                    break;
                default:
                    // 不明なルールの場合は無視する
                    break;
            }
        }
    }

    /**
     * ルールのチェック
     *
     * @param Collection<\App\Domain\Resource\Entities\Unit> $unitEntities
     *
     * @return void
     * @throws \Throwable
     */
    public function checkRules(Collection $unitEntities): void
    {
        $violationRuleInfos = collect();
        foreach ($this->ruleCheckers as $ruleChecker) {
            /** @var \App\Domain\Party\Manager\Checker\IRuleChecker $ruleChecker */
            if (!$ruleChecker->checkRule($unitEntities)) {
                // ルールに適してない場合
                $violationRuleInfos->push($ruleChecker->getRuleInfo());
            }
        }

        if ($violationRuleInfos->isNotEmpty()) {
            throw new GameException(
                ErrorCode::STAGE_EVENT_PARTY_VIOLATION_RULE,
                sprintf(
                    'stage event violation rule(%s)',
                    $violationRuleInfos->implode(', ')
                )
            );
        }
    }
}
