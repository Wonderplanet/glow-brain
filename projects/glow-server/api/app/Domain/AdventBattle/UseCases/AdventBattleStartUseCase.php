<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Services\AdventBattleLogService;
use App\Domain\AdventBattle\Services\AdventBattleStartCheatService;
use App\Domain\AdventBattle\Services\AdventBattleStartService;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRepository;
use App\Http\Responses\ResultData\AdventBattleStartResultData;

class AdventBattleStartUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private readonly AdventBattleStartService $adventBattleStartService,
        private readonly AdventBattleStartCheatService $adventBattleStartCheatService,
        private readonly AdventBattleLogService $adventBattleLogService,
        // Repository
        private readonly MstAdventBattleRepository $mstAdventBattleRepository,
        // Other
        private readonly Clock $clock,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param string $mstAdventBattleId
     * @param int $partyNo
     * @param bool $isChallengeAd
     * @param array<mixed> $inGameBattleLog
     * @return AdventBattleStartResultData
     * @throws \Throwable
     */
    public function exec(
        CurrentUser $user,
        string $mstAdventBattleId,
        int $partyNo,
        bool $isChallengeAd,
        array $inGameBattleLog,
    ): AdventBattleStartResultData {
        $usrUserId = $user->id;
        $now = $this->clock->now();

        $inGameBattleLogData = $this->adventBattleLogService->makeInGameBattleLogData($inGameBattleLog);
        $mstAdventBattle = $this->mstAdventBattleRepository->getActive($mstAdventBattleId, $now, true);
        $usrAdventBattle = $this->adventBattleStartService->start(
            $usrUserId,
            $partyNo,
            $mstAdventBattle,
            $isChallengeAd,
            $now,
        );

        $this->adventBattleStartCheatService->checkCheat(
            $inGameBattleLogData,
            $usrAdventBattle,
            $now,
            $partyNo,
            $mstAdventBattle->getEventBonusGroupId(),
        );

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new AdventBattleStartResultData();
    }
}
