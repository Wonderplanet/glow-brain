<?php

declare(strict_types=1);

namespace App\Domain\Party\Delegators;

use App\Domain\Party\Services\PartyEventRuleService;
use App\Domain\Party\Services\PartyService;
use App\Domain\Resource\Entities\Party;
use App\Domain\Resource\Mst\Entities\MstInGameSpecialRuleEntity;
use Illuminate\Support\Collection;

class PartyDelegator
{
    public function __construct(
        private PartyService $partyService,
        private PartyEventRuleService $partyEventRuleService,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param int    $partyNo
     * @return Party
     */
    public function getParty(string $usrUserId, int $partyNo): Party
    {
        return $this->partyService->getParty($usrUserId, $partyNo);
    }

    /**
     * @param string $usrUserId
     * @param int $partyNo
     * @param Collection<MstInGameSpecialRuleEntity> $mstStageEventRules
     * @return Party
     * @throws \Throwable
     */
    public function checkAndGetPartyForRules(
        string $usrUserId,
        int $partyNo,
        Collection $mstStageEventRules
    ): Party {
        return $this->partyEventRuleService->checkAndGetPartyForRules($usrUserId, $partyNo, $mstStageEventRules);
    }

    public function makePartyStatusList(Collection $partyStatuses): Collection
    {
        return $this->partyService->makePartyStatusList($partyStatuses);
    }
}
