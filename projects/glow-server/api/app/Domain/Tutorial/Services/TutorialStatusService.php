<?php

declare(strict_types=1);

namespace App\Domain\Tutorial\Services;

use App\Domain\Gacha\Delegators\GachaDelegator;
use App\Domain\IdleIncentive\Delegators\IdleIncentiveDelegator;
use App\Domain\Resource\Mst\Entities\MstTutorialEntity;
use App\Domain\Resource\Mst\Repositories\MstTutorialRepository;
use App\Domain\Tutorial\Enums\TutorialType;
use App\Domain\Tutorial\Repositories\LogTutorialActionRepository;
use App\Domain\Tutorial\Repositories\UsrTutorialRepository;
use App\Domain\User\Delegators\UserDelegator;
use App\Http\Responses\Data\UsrTutorialData;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * チュートリアルステータス関連ロジックのサービスクラス
 */
class TutorialStatusService
{
    public function __construct(
        // MstRepository
        private MstTutorialRepository $mstTutorialRepository,
        // UsrRepository
        private UsrTutorialRepository $usrTutorialRepository,
        // LogRepository
        private LogTutorialActionRepository $logTutorialActionRepository,
        // Delegator
        private UserDelegator $userDelegator,
        private GachaDelegator $gachaDelegator,
        private IdleIncentiveDelegator $idleIncentiveDelegator,
    ) {
    }

    public function updateTutorialStatus(
        string $usrUserId,
        CarbonImmutable $now,
        string $mstTutorialFunctionName,
        int $platform,
    ): void {
        /** @var MstTutorialEntity $targetMstTutorial */
        $targetMstTutorial = $this->mstTutorialRepository->getActiveByFunctionName(
            $mstTutorialFunctionName,
            $now,
            isThrowError: true,
        );

        switch ($targetMstTutorial->getType()) {
            case TutorialType::INTRO->value:
            case TutorialType::MAIN->value:
                $isCompleteMainPart = $this->userDelegator->updateIntroAndMainPartStatus(
                    $usrUserId,
                    $now,
                    $targetMstTutorial
                );
                if ($isCompleteMainPart) {
                    $this->onMainPartCompleted($usrUserId, $now, $platform);
                }
                break;
            case TutorialType::FREE->value:
                $this->updateFreePartStatus($usrUserId, $targetMstTutorial);
                break;
            default:
                break;
        }

        // チュートリアルの進捗ログを保存
        $this->logTutorialActionRepository->create(
            $usrUserId,
            $mstTutorialFunctionName,
        );
    }

    /**
     * フリーパートのチュートリアルステータスを更新する
     *
     * フリーパートの順序は厳密に確認しない
     */
    public function updateFreePartStatus(
        string $usrUserId,
        MstTutorialEntity $mstTutorial,
    ): void {
        $this->usrTutorialRepository->getOrCreate(
            $usrUserId,
            $mstTutorial->getId(),
        );
    }

    /**
     * 完了済みのフリーパートチュートリアルを取得する
     *
     * @return Collection<UsrTutorialData>
     */
    public function getCompletedFreePartUsrTutorials(string $usrUserId, CarbonImmutable $now): Collection
    {
        $result = collect();

        $mstTutorials = $this->mstTutorialRepository->getActivesByType(
            TutorialType::FREE,
            $now,
        )->keyBy->getId();
        $usrTutorials = $this->usrTutorialRepository->getByMstTutorialIds(
            $usrUserId,
            $mstTutorials->keys(),
        );

        foreach ($usrTutorials as $usrTutorial) {
            $mstTutorial = $mstTutorials->get($usrTutorial->getMstTutorialId());
            if ($mstTutorial === null) {
                continue;
            }

            $result->push(
                new UsrTutorialData(
                    $mstTutorial->getFunctionName(),
                )
            );
        }

        return $result;
    }

    /**
     * メインパート完了時に実行する処理をまとめる
     */
    private function onMainPartCompleted(string $usrUserId, CarbonImmutable $now, int $platform): void
    {
        $this->userDelegator->incrementLoginCountAndProcessActions($usrUserId, $platform, $now);

        $this->gachaDelegator->unlockTutorialCompleteGacha($usrUserId, $now);

        $this->idleIncentiveDelegator->setIdleStartedAtNow($usrUserId, $now);
    }
}
