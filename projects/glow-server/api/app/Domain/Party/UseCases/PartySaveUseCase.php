<?php

declare(strict_types=1);

namespace App\Domain\Party\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Party\Services\PartyService;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Unit\Delegators\UnitDelegator;
use App\Http\Responses\ResultData\PartySaveResultData;

class PartySaveUseCase
{
    use UseCaseTrait;

    public function __construct(
        private PartyService $partyService,
        private UnitDelegator $unitDelegator,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param array<mixed> $parties
     * [
     *     {
     *         'partyNo': int,
     *         'partyName': string,
     *         'units': [string, string, ...]
     *     }, ...
     * ]
     * @return PartySaveResultData
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, array $parties): PartySaveResultData
    {
        $usrUserId = $user->getUsrUserId();

        $usrUnitIds = $this
            ->unitDelegator
            ->getUsrUnitsByUsrUserId($usrUserId)
            ->map(fn(UsrUnitEntity $usrUnit) => $usrUnit->getUsrUnitId());
        $usrParties = $this->partyService->saveParties($usrUserId, $parties, $usrUnitIds);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new PartySaveResultData($usrParties);
    }
}
