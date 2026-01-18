<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Services\AdventBattleRankingService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Http\Responses\ResultData\AdventBattleRankingResultData;

class AdventBattleRankingUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Repositories
        private readonly MstAdventBattleRepository $mstAdventBattleRepository,
        // Services
        private readonly AdventBattleRankingService $adventBattleRankingService,
        // Common
        private readonly Clock $clock,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $mstAdventBattleId,
        bool $isPrevious = false
    ): AdventBattleRankingResultData {
        if ($isPrevious) {
            $mstAdventBattleId = $this->mstAdventBattleRepository->getPreviousMstAdventBattle(
                $mstAdventBattleId,
            )->getId();
        }
        $adventBattleRankingData = $this->adventBattleRankingService->getRanking(
            $user->id,
            $mstAdventBattleId,
            $this->clock->now()
        );
        $this->processWithoutUserTransactionChanges();

        return new AdventBattleRankingResultData($adventBattleRankingData);
    }
}
