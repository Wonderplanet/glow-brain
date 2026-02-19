<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Factories\AdventBattleServiceFactory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\Resource\Mst\Entities\MstAdventBattleEntity;
use App\Domain\Reward\Delegators\RewardDelegator;

class AdventBattleTopService
{
    public function __construct(
        // Repositories
        private readonly UsrAdventBattleRepository $usrAdventBattleRepository,
        // Factories
        private readonly AdventBattleServiceFactory $adventBattleServiceFactory,
        // Delegator
        private readonly RewardDelegator $rewardDelegator,
    ) {
    }

    /**
     * 降臨バトルのレイド報酬を受け取る
     *
     * @param MstAdventBattleEntity $mstAdventBattle
     * @param ?UsrAdventBattleInterface $usrAdventBattle
     * @throws \App\Domain\Common\Exceptions\GameException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function addRaidTotalScoreReward(
        MstAdventBattleEntity $mstAdventBattle,
        ?UsrAdventBattleInterface $usrAdventBattle,
    ): void {
        if (is_null($usrAdventBattle) || !$mstAdventBattle->isRaid()) {
            return;
        }

        /** @var \App\Domain\AdventBattle\Services\AdventBattleRewardRaidTotalScoreService $raidRewardService */
        $raidRewardService = $this->adventBattleServiceFactory->getAdventBattleRewardService(
            AdventBattleRewardCategory::RAID_TOTAL_SCORE->value
        );
        $adventBattleReceivableReward = $raidRewardService->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();
        if ($mstAdventBattleRewards->isEmpty()) {
            return;
        }

        $raidRewardService->setLatestReceivedRewardGroupId(
            $usrAdventBattle,
            $adventBattleReceivableReward->getLatestMstAdventBattleRewardGroupId(),
        );

        $adventBattleRaidRewards = $raidRewardService->convertRewards(
            $mstAdventBattle->getId(),
            $mstAdventBattleRewards
        );
        $this->rewardDelegator->addRewards($adventBattleRaidRewards);
    }

    public function addMaxScoreReward(
        MstAdventBattleEntity $mstAdventBattle,
        ?UsrAdventBattleInterface $usrAdventBattle,
    ): void {
        if (is_null($usrAdventBattle)) {
            return;
        }

        /** @var \App\Domain\AdventBattle\Services\AdventBattleRewardMaxScoreService $raidRewardService */
        $raidRewardService = $this->adventBattleServiceFactory->getAdventBattleRewardService(
            AdventBattleRewardCategory::MAX_SCORE->value
        );
        $adventBattleReceivableReward = $raidRewardService->fetchAvailableRewards($usrAdventBattle);
        $mstAdventBattleRewards = $adventBattleReceivableReward->getMstAdventBattleRewards();

        if ($mstAdventBattleRewards->isNotEmpty()) {
            $rewards = $raidRewardService->convertRewards($mstAdventBattle->getId(), $mstAdventBattleRewards);
            $this->rewardDelegator->addRewards($rewards);
        }
        // 受け取った報酬の最大スコアを更新(クライアントの演出の都合で受け取っていないくても最大スコアに更新する)
        $usrAdventBattle->receiveMaxScoreReward();
        $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
    }
}
