<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Repositories\UsrAdventBattleSessionRepository;
use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;

readonly class AdventBattleCleanupService
{
    public function __construct(
        private UsrAdventBattleSessionRepository $usrAdventBattleSessionRepository,
    ) {
    }

    public function cleanup(string $usrUserId): void
    {
        $usrAdventBattleSession = $this->usrAdventBattleSessionRepository->findByUsrUserId($usrUserId);

        // セッションが存在しない、または既にclosedの場合は何もしない
        if (
            is_null($usrAdventBattleSession)
            || $usrAdventBattleSession->isClosed()
        ) {
            // エラーで返す様にする
            throw new GameException(ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        }

        $usrAdventBattleSession->closeSession();
        $this->usrAdventBattleSessionRepository->syncModel($usrAdventBattleSession);
    }
}
