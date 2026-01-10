<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\User\Repositories\UsrUserRepository;
use Carbon\CarbonImmutable;

class UserTutorialService
{
    public function __construct(
        // MstRepository
        private MstTutorialRepository $mstTutorialRepository,
        // UsrRepository
        private UsrUserRepository $usrUserRepository,
    ) {
    }

    public function getTutorialStatus(string $usrUserId): string
    {
        return $this->usrUserRepository->findById($usrUserId)->getTutorialStatus();
    }

    /**
     * イントロとメインパートのチュートリアルステータスを更新する
     *
     * @return bool メインパート完了へ更新する場合true、それ以外false(trueになるのはメインパート完了への更新時1回のみ)
     * @throws GameException
     */
    public function updateIntroAndMainPartStatus(
        string $usrUserId,
        CarbonImmutable $now,
        MstTutorialEntity $mstTutorial,
    ): bool {
        $mstTutorialFunctionName = $mstTutorial->getFunctionName();

        $usrUser = $this->usrUserRepository->findById($usrUserId);
        $currentMstFunctionName = $usrUser->getTutorialStatus();

        $isUpdate = false;
        if ($usrUser->isTutorialUnplayed() && $mstTutorial->getSortOrder() === 1) {
            // チュートリアルプレイ初回
            $isUpdate = true;
        } else {
            // チュートリアルプレイ2回目以降
            /** @var MstTutorialEntity $currentMstTutorial */
            $currentMstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
                $currentMstFunctionName,
                $now,
                isThrowError: true,
            );

            if (
                ($currentMstTutorial->getSortOrder() + 1) === $mstTutorial->getSortOrder()
            ) {
                $isUpdate = true;
            }
        }

        if ($isUpdate) {
            // チュートリアルステータスの更新
            $usrUser->setTutorialStatus($mstTutorialFunctionName);
            $this->usrUserRepository->syncModel($usrUser);

            // メインパート完了場合true、それ以外false
            return $mstTutorial->isMainPartCompleted();
        }

        throw new GameException(
            ErrorCode::TUTORIAL_INVALID_MAIN_PART_ORDER,
            sprintf(
                'The progress of the tutorial is in an unexpected order. %s -> %s',
                $currentMstFunctionName,
                $mstTutorialFunctionName
            ),
        );
    }
}
