<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Tutorial\Enums\TutorialFunctionName;
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

            /**
             * チュートリアルの更新があり、旧チュートリアルのガシャまで完了している場合は新チュートリアルへ移行せず完了とさせる
             * ガシャまで完了していない場合は新チュートリアルへ移行する
             */
            // 旧チュートリアルの完了更新の基準となるガシャチュートリアル
            /** @var MstTutorialEntity $gachaConfirmedMstTutorial */
            $gachaConfirmedMstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
                TutorialFunctionName::GACHA_CONFIRMED->value,
                $now,
                isThrowError: true,
            );

            // 旧チュートリアルの完了の1つ前のチュートリアル
            /** @var MstTutorialEntity $startMainPart3MstTutorial */
            $startMainPart3MstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
                TutorialFunctionName::START_MAIN_PART3->value,
                $now,
                isThrowError: true,
            );

            if (($currentMstTutorial->getSortOrder() + 1) === $mstTutorial->getSortOrder()) {
                // 順番通りの更新
                $isUpdate = true;
            } elseif ($currentMstTutorial->getSortOrder() < $gachaConfirmedMstTutorial->getSortOrder()) {
                // 旧チュートリアルから新チュートリアルへの移行のためsort_orderが連番以外でも許容する
                // ただし完了(MainPartCompleted)への更新は不許可
                if (!$mstTutorial->isMainPartCompleted()) {
                    $isUpdate = true;
                }
            } elseif ($currentMstTutorial->getSortOrder() <= $startMainPart3MstTutorial->getSortOrder()) {
                // 旧チュートリアルのガシャまで完了しているユーザーはチュートリアルを完了させるため完了への更新を許容する
                if ($mstTutorial->isMainPartCompleted()) {
                    $isUpdate = true;
                }
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
