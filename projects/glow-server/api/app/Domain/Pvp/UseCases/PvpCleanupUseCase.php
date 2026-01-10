<?php

declare(strict_types=1);

namespace App\Domain\Pvp\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Pvp\Repositories\UsrPvpSessionRepository;
use App\Http\Responses\ResultData\PvpCleanupResultData;

class PvpCleanupUseCase
{
    use UseCaseTrait;

    public function __construct(
        private UsrPvpSessionRepository $usrPvpSessionRepository,
    ) {
    }

    public function exec(CurrentUser $user): PvpCleanupResultData
    {
        // pvpはendでコスト消費のためセッションを閉じるだけ
        $usrPvpSession = $this->usrPvpSessionRepository->findByUsrUserId($user->id);

        if ($usrPvpSession === null || $usrPvpSession->isClosed()) {
            throw new GameException(ErrorCode::CONTENT_MAINTENANCE_SESSION_CLEANUP_FAILED);
        }

        $usrPvpSession->closeSession();
        $this->usrPvpSessionRepository->syncModel($usrPvpSession);

        $this->applyUserTransactionChanges();

        return new PvpCleanupResultData();
    }
}
