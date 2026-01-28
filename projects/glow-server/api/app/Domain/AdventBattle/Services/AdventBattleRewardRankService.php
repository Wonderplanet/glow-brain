<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleReceivableReward;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\Resource\Entities\Rewards\AdventBattleRankReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardRepository;

class AdventBattleRewardRankService extends AdventBattleRewardService
{
    public function __construct(
        MstAdventBattleRewardRepository $mstAdventBattleRewardRepository,
        MstAdventBattleRewardGroupRepository $mstAdventBattleRewardGroupRepository,
        private readonly AdventBattleRankService $adventBattleRankService,
        private readonly UsrAdventBattleRepository $usrAdventBattleRepository,
    ) {
        parent::__construct(
            $mstAdventBattleRewardRepository,
            $mstAdventBattleRewardGroupRepository,
            AdventBattleRewardCategory::RANK,
            AdventBattleRankReward::class,
        );
    }

    /**
     * @inheritdoc
     */
    public function fetchAvailableRewards(UsrAdventBattleInterface $usrAdventBattle): AdventBattleReceivableReward
    {
        if ($usrAdventBattle->isRankingRewardReceived()) {
            // 報酬受け取り済み
            return $this->createAdventBattleReceivableReward(collect());
        }

        $mstAdventBattleId = $usrAdventBattle->getMstAdventBattleId();
        $mstAdventBattleRanks = $this->adventBattleRankService->getRanksByTotalScore(
            $mstAdventBattleId,
            $usrAdventBattle->getTotalScore(),
        );
        if ($mstAdventBattleRanks->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // 該当ランクの報酬を全て取得
        $mstAdventBattleRankIds = $mstAdventBattleRanks->map(fn($entity) => $entity->getId());
        $mstAdventBattleRewardGroups = $this->getRewardGroups($mstAdventBattleId)
            ->filter(function ($entity) use ($mstAdventBattleRankIds) {
                /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity $entity */
                return $mstAdventBattleRankIds->contains($entity->getConditionValue());
            })->sortBy(function ($entity) use ($mstAdventBattleRankIds) {
                /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity $entity */
                return $mstAdventBattleRankIds->search($entity->getConditionValue());
            })->values();
        if ($mstAdventBattleRewardGroups->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // 取得済み報酬を除外
        $unreceivedRewardGroupIds = $this->getUnreceivedRewardGroupIds(
            $usrAdventBattle->getReceivedRankRewardGroupId(),
            $mstAdventBattleRewardGroups->map(fn($entity) => $entity->getId()),
        );
        if ($unreceivedRewardGroupIds->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // 報酬グループから報酬を取得して返す
        return $this->createAdventBattleReceivableReward(
            $unreceivedRewardGroupIds,
            $unreceivedRewardGroupIds->last()
        );
    }

    /**
     * 直近で受け取った報酬グループIDを設定
     *
     * @param UsrAdventBattleInterface $usrAdventBattle
     * @param string $mstAdventBattleRewardGroupId
     */
    public function setLatestReceivedRewardGroupId(
        UsrAdventBattleInterface $usrAdventBattle,
        string $mstAdventBattleRewardGroupId,
    ): void {
        $usrAdventBattle->setReceivedRankRewardGroupId($mstAdventBattleRewardGroupId);
        $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
    }
}
