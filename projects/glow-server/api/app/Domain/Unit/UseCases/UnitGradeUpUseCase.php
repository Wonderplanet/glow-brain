<?php

declare(strict_types=1);

namespace App\Domain\Unit\UseCases;

use App\Domain\Common\Entities\Clock;
use App\Domain\Common\Entities\CurrentUser;
use App\Domain\Common\Traits\UseCaseTrait;
use App\Domain\Resource\Entities\Rewards\UnitGradeUpReward;
use App\Domain\Resource\Usr\Services\UsrModelDiffGetService;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Services\UnitGradeUpRewardService;
use App\Domain\Unit\Services\UnitGradeUpService;
use App\Http\Responses\ResultData\UnitGradeUpResultData;

class UnitGradeUpUseCase
{
    use UseCaseTrait;

    public function __construct(
        private Clock $clock,
        private UnitGradeUpService $unitGradeUpService,
        private UsrModelDiffGetService $usrModelDiffGetService,
        private UnitGradeUpRewardService $unitGradeUpRewardService,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    public function exec(CurrentUser $user, string $usrUnitId, int $platform): UnitGradeUpResultData
    {
        $usrUnit = $this->unitGradeUpService->gradeUp($usrUnitId, $user->id);

        // 原画報酬付与判定
        $this->unitGradeUpRewardService->grantGradeUpReward($usrUnit);

        // トランザクション処理
        $this->applyUserTransactionChanges(function () use ($user, $platform) {
            $this->rewardDelegator->sendRewards($user->id, $platform, $this->clock->now());
        });

        // レスポンス作成
        return new UnitGradeUpResultData(
            $usrUnit,
            $this->usrModelDiffGetService->getChangedUsrItems(),
            $this->usrModelDiffGetService->getChangedUsrArtworks(),
            $this->usrModelDiffGetService->getChangedUsrArtworkFragments(),
            $this->rewardDelegator->getSentRewards(UnitGradeUpReward::class),
        );
    }
}
