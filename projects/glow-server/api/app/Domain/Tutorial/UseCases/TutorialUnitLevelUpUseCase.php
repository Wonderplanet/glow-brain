<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Tutorial\Services\TutorialStatusService;
use App\Domain\Unit\Delegators\UnitTutorialDelegator;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\ResultData\TutorialUnitLevelUpResultData;

class TutorialUnitLevelUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        // Repository
        private MstTutorialRepository $mstTutorialRepository,
        // Service
        private TutorialStatusService $tutorialStatusService,
        // Delegator
        private UserDelegator $userDelegator,
        private UnitTutorialDelegator $unitTutorialDelegator,
    ) {
    }

    public function exec(
        CurrentUser $user,
        string $mstTutorialFunctionName,
        string $usrUnitId,
        int $level,
        int $platform
    ): TutorialUnitLevelUpResultData {

        $usrUserId = $user->id;
        $now = $this->clock->now();

        /** @var MstTutorialEntity $mstTutorial */
        $mstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
            $mstTutorialFunctionName,
            $now,
            isThrowError: true,
        );

        // ユニットレベルアップ
        $usrUnit = $this->unitTutorialDelegator->levelUp($usrUserId, $usrUnitId, $level, $now);

        // チュートリアルステータス更新
        $this->tutorialStatusService->updateTutorialStatus($usrUserId, $now, $mstTutorialFunctionName, $platform);

        // トランザクション処理
        $this->applyUserTransactionChanges();

        return new TutorialUnitLevelUpResultData(
            $mstTutorialFunctionName,
            $usrUnit,
            $this->makeUsrParameterData($this->userDelegator->getUsrUserParameterByUsrUserId($usrUserId))
        );
    }
}
