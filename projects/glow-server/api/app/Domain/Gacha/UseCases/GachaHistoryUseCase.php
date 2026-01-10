<?php

declare(strict_types=1);

namespace App\Domain\Gacha\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Gacha\Services\GachaService;
use App\Http\Responses\ResultData\GachaHistoryResultData;

class GachaHistoryUseCase
{
    use UseCaseTrait;

    public function __construct(
        private GachaService $gachaService,
        private Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @return GachaHistoryResultData
     */
    public function exec(CurrentUser $user): GachaHistoryResultData
    {
        $usrUserId = $user->id;
        $gachaHistories = $this->gachaService->getGachaHistories($usrUserId, $this->clock->now());
        $this->processWithoutUserTransactionChanges();
        return new GachaHistoryResultData($gachaHistories);
    }
}
