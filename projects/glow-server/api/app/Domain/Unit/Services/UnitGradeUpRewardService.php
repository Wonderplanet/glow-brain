<?php

declare(strict_types=1);

namespace App\Domain\Unit\Services;

use App\Domain\Common\Exceptions\GameException;
use App\Domain\Resource\Entities\Rewards\UnitGradeUpReward;
use App\Domain\Resource\Mst\Repositories\MstUnitGradeUpRewardRepository;
use App\Domain\Reward\Delegators\RewardDelegator;
use App\Domain\Unit\Models\UsrUnitInterface;
use App\Domain\Unit\Repositories\UsrUnitRepository;

readonly class UnitGradeUpRewardService
{
    public function __construct(
        private MstUnitGradeUpRewardRepository $mstUnitGradeUpRewardRepository,
        private UsrUnitRepository $usrUnitRepository,
        private RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * グレードアップ時の報酬を付与する
     *
     * @param UsrUnitInterface $usrUnit
     * @return bool 付与された場合はtrue、付与されなかった場合はfalse
     * @throws GameException
     */
    public function grantGradeUpReward(UsrUnitInterface $usrUnit): bool
    {
        $gradeLevel = $usrUnit->getGradeLevel();
        $lastRewardGradeLevel = $usrUnit->getLastRewardGradeLevel();

        // 既にこのグレードで受け取り済みの場合は何もしない
        if ($lastRewardGradeLevel >= $gradeLevel) {
            return false;
        }

        // lastRewardGradeLevelから現在のgradeLevelまでの報酬を取得
        $mstRewards = $this->mstUnitGradeUpRewardRepository->getByMstUnitIdAndGradeLevel(
            $usrUnit->getMstUnitId(),
            $lastRewardGradeLevel,
            $gradeLevel
        );
        if ($mstRewards->isEmpty()) {
            return false;
        }

        $usrUnit->setLastRewardGradeLevel($gradeLevel);
        $this->usrUnitRepository->syncModel($usrUnit);

        // 複数の報酬をループして付与
        foreach ($mstRewards as $mstReward) {
            // 報酬を作成してRewardDelegatorを介して付与
            $reward = new UnitGradeUpReward(
                $mstReward->getResourceType(),
                $mstReward->getResourceId(),
                $mstReward->getResourceAmount(),
                $mstReward->getId(),
            );
            $this->rewardDelegator->addReward($reward);
        }

        return true;
    }
}
