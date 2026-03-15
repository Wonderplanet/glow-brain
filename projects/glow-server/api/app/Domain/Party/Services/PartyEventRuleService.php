<?php

declare(strict_types=1);

namespace App\Domain\Party\Services;

use App\Domain\Party\Manager\RuleCheckManager;
use App\Domain\Resource\Entities\Party;
use Illuminate\Support\Collection;

class PartyEventRuleService
{
    public function __construct(
        // Service
        private PartyService $partyService,
        // Manager
        protected RuleCheckManager $ruleCheckManager,
    ) {
    }

    /**
     * 対象パーティがルールに適しているかチェックする
     *
     * @param string $usrUserId
     * @param int $partyNo
     * @param Collection<\App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity> $mstInGameSpecialRules
     * @return Party
     * @throws \Throwable
     */
    public function checkAndGetPartyForRules(
        string $usrUserId,
        int $partyNo,
        Collection $mstInGameSpecialRules,
    ): Party {
        $party = $this->partyService->getParty($usrUserId, $partyNo);
        if (!$mstInGameSpecialRules->isEmpty()) {
            $this->ruleCheckManager->setRules($mstInGameSpecialRules);
            $this->ruleCheckManager->checkRules($party->getUnits());
        }
        return $party;
    }
}
