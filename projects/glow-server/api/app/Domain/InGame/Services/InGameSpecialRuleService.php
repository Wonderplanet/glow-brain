<?php

declare(strict_types=1);

namespace App\Domain\InGame\Services;

use App\Domain\Party\Delegators\PartyDelegator;
use App\Domain\Resource\Entities\Party;
use App\Domain\Resource\Enums\InGameContentType;
use App\Domain\Resource\Mst\Repositories\MstInGameSpecialRuleRepository;
use Carbon\CarbonImmutable;

class InGameSpecialRuleService
{
    public function __construct(
        // Repository
        private MstInGameSpecialRuleRepository $mstInGameSpecialRuleRepository,
        // Delegator
        private PartyDelegator $partyDelegator, // TODO: PartyドメインをInGameドメインへ統合検討
    ) {
    }

    /**
     * 指定パーティを構成するユニット全てをチェックしてルールに反していないことを確認
     * 反していたらエラー
     *
     * @param string $usrUserId
     * @param int $partyNo usr_parties.party_no
     * @param \App\Domain\Resource\Enums\InGameContentType $inGameContentType
     * @param string $targetId
     * @param \Carbon\CarbonImmutable $now
     * @return Party
     * @throws \Throwable
     */
    public function checkAndGetParty(
        string $usrUserId,
        int $partyNo,
        InGameContentType $inGameContentType,
        string $targetId,
        CarbonImmutable $now,
    ): Party {
        $mstInGameSpecialRules = $this->mstInGameSpecialRuleRepository->getByContentTypeAndTargetId(
            $inGameContentType,
            $targetId,
            $now,
        );

        return $this->partyDelegator->checkAndGetPartyForRules($usrUserId, $partyNo, $mstInGameSpecialRules);
    }
}
