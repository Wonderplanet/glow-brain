<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Stage\Models\UsrStageSessionInterface;
use App\Domain\Stage\Repositories\UsrStageSessionRepository;
use App\Http\Responses\ResultData\StageCleanupResultData;

class StageCleanupUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Repository
        private UsrStageSessionRepository $usrStageSessionRepository,
        // Common
        private Clock $clock,
    ) {
    }

    public function exec(CurrentUser $user): StageCleanupResultData
    {
        $usrUserId = $user->id;
        $now = $this->clock->now();
        $usrStageSession = $this->usrStageSessionRepository->get($usrUserId, $now);

        /** @var UsrStageSessionInterface $usrStageSession */
        if ($usrStageSession->isClosed()) {
            throw new GameException(ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        }

        $usrStageSession->closeSession();
        $this->usrStageSessionRepository->syncModel($usrStageSession);

        $this->applyUserTransactionChanges();

        return new StageCleanupResultData();
    }
}
