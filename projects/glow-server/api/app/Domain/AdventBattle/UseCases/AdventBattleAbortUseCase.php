<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\UseCases;

use App\Domain\AdventBattle\Services\AdventBattleAbortService;
use App\Domain\AdventBattle\Services\AdventBattleCacheService;
use App\Domain\AdventBattle\Services\AdventBattleLogService;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Http\Responses\ResultData\AdventBattleAbortResultData;

class AdventBattleAbortUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private readonly AdventBattleAbortService $adventBattleAbortService,
        private readonly AdventBattleLogService $adventBattleLogService,
        private readonly AdventBattleCacheService $adventBattleCacheService,
    ) {
    }

    /**
     * @param CurrentUser $user
     * @param int $abortType
     * @return AdventBattleAbortResultData
     * @throws \Throwable
     */
    public function exec(CurrentUser $user, int $abortType): AdventBattleAbortResultData
    {
        $usrAdventBattleSession = $this->adventBattleAbortService->abort($user->id);
        $this->adventBattleLogService->sendAbortLog($user->id, $abortType);

        $mstAdventBattleId = $usrAdventBattleSession->getMstAdventBattleId();
        $allUserTotalScore = $this->adventBattleCacheService->getRaidTotalScore($mstAdventBattleId);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new AdventBattleAbortResultData($allUserTotalScore);
    }
}
