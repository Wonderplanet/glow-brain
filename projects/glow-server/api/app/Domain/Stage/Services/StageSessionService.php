<?php

declare(strict_types=1);

namespace App\Domain\Stage\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use Carbon\CarbonImmutable;

class StageSessionService
{
    public function __construct(
        private UsrStageSessionRepository $usrStageSessionRepository,
        private Clock $clock,
    ) {
    }

    /**
     * ステージセッションをリセットして取得する
     *
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return ?UsrStageSessionInterface
     */
    public function getUsrStageSessionWithResetDaily(string $usrUserId, CarbonImmutable $now): ?UsrStageSessionInterface
    {
        $usrStageSession = $this->usrStageSessionRepository->findByUsrUserId($usrUserId);
        if (!is_null($usrStageSession) && $this->clock->isFirstToday($usrStageSession->getLatestResetAt())) {
            $usrStageSession->resetDaily($now);
            $this->usrStageSessionRepository->syncModel($usrStageSession);
        }
        return $usrStageSession;
    }

    /**
     * ステージが開始されているかを検証する
     *
     * @param ?UsrStageSessionInterface $usrStageSession
     * @param string $mstStageId
     * @return void
     * @throws GameException
     */
    public function validateStageStarted(?UsrStageSessionInterface $usrStageSession, string $mstStageId): void
    {
        if (is_null($usrStageSession) || !$usrStageSession->isStartedByMstStageId($mstStageId)) {
            throw new GameException(
                ErrorCode::STAGE_NOT_START,
                "stage not started (mst_stage_id: $mstStageId)",
            );
        }
    }
}
