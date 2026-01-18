<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Services\AdventBattleRankingService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Http\Responses\ResultData\AdventBattleInfoResultData;

readonly class AdventBattleInfoUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Services
        private AdventBattleRankingService $adventBattleRankingService,
        // Common
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): AdventBattleInfoResultData
    {
        $adventBattleResultData = $this->adventBattleRankingService->getAdventBattleResultData(
            $user->id,
            $this->clock->now()
        );
        $this->processWithoutUserTransactionChanges();

        return new AdventBattleInfoResultData($adventBattleResultData);
    }
}
