<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Common\Constants\ErrorCode;
use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Exceptions\GameException;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Entities\Rewards\UnitGradeUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Repositories\UsrUnitRepository;
use App\Domain\Unit\Services\UnitGradeUpRewardService;
use App\Http\Responses\ResultData\UnitReceiveGradeUpRewardResultData;

class UnitReceiveGradeUpRewardUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UnitGradeUpRewardService $unitGradeUpRewardService,
        private UsrUnitRepository $usrUnitRepository,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param string $usrUnitId
     * @param int $platform
     * @return UnitReceiveGradeUpRewardResultData
     * @throws GameException
     */
    public function exec(string $usrUserId, string $usrUnitId, int $platform): UnitReceiveGradeUpRewardResultData
    {
        $usrUnit = $this->usrUnitRepository->getById($usrUnitId, $usrUserId);

        // 既に受け取り済みの場合はエラー
        if ($usrUnit->getLastRewardGradeLevel() === $usrUnit->getGradeLevel()) {
            throw new GameException(ErrorCode::INVALID_PARAMETER, 'Already received grade up reward');
        }

        // 報酬付与実行
        $isGranted = $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);
        if ($isGranted === false) {
            throw new GameException(ErrorCode::MST_NOT_FOUND, 'Grade up reward master data not found');
        }

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($usrUserId, $platform) {
            $this->rewardDelegator->sendRewards($usrUserId, $platform, $this->clock->now());
        });

        return new UnitReceiveGradeUpRewardResultData(
            $usrUnit,
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
            $this->rewardDelegator->getSentRewards(UnitGradeUpReward::class),
        );
    }
}
