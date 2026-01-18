<?php

declare(strict_types=1);

namespace App\Domain\Stage\UseCases;

use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Stage\Services\StageLogService;
use App\Domain\Stage\Services\StageService;
use App\Http\Responses\ResultData\StageAbortResultData;

class StageAbortUseCase
{
    use UseCaseTrait;

    public function __construct(
        // Service
        private StageService $stageService,
        private StageLogService $stageLogService,
    ) {
    }

    /**
     * ステージリタイア/敗北/中断復帰キャンセル/期限切れ処理
     *
     * @param CurrentUser $user
     * @return StageAbortResultData
     * @throws \App\Domain\Common\Exceptions\GameException
     */
    public function exec(CurrentUser $user, int $abortType): StageAbortResultData
    {
        $usrStageSession = $this->stageService->abort($user->id);
        $lapCount = $usrStageSession?->getAutoLapCount() ?? 0;

        $this->stageLogService->sendAbortLog(
            $user->id,
            $abortType,
            $lapCount,
        );

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new StageAbortResultData();
    }
}
