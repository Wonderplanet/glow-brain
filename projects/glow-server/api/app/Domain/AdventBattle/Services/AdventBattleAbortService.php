<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Models\UsrAdventBattleSessionInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;

class AdventBattleAbortService
{
    public function __construct(
        // Repository
        protected readonly UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
    ) {
    }


    /**
     * 降臨バトルをリタイア/中断復帰キャンセルする
     * @param string $usrUserId
     * @return UsrAdventBattleSessionInterface
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function abort(string $usrUserId): UsrAdventBattleSessionInterface
    {
        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findWithError($usrUserId, true);
        $usrAdventBattleSession->closeSession();
        $this->usrAdventBattleSessionRepository->syncModel($usrAdventBattleSession);
        return $usrAdventBattleSession;
    }
}
