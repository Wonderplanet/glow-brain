<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleReceivableReward;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\AdventBattle\Repositories\UsrAdventBattleRepository;
use App\Domain\Resource\Entities\Rewards\AdventBattleRaidTotalScoreReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardRepository;

class AdventBattleRewardRaidTotalScoreService extends AdventBattleRewardService
{
    public function __construct(
        MstAdventBattleRewardRepository $mstAdventBattleRewardRepository,
        MstAdventBattleRewardGroupRepository $mstAdventBattleRewardGroupRepository,
        private readonly AdventBattleCacheService $adventBattleCacheService,
        private readonly UsrAdventBattleRepository $usrAdventBattleRepository,
    ) {
        parent::__construct(
            $mstAdventBattleRewardRepository,
            $mstAdventBattleRewardGroupRepository,
            AdventBattleRewardCategory::RAID_TOTAL_SCORE,
            AdventBattleRaidTotalScoreReward::class
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
        $raidTotalScore = $this->adventBattleCacheService->getRaidTotalScore($mstAdventBattleId);

        // 協力バトル全ユーザー累計スコアに到達している報酬グループ全てを取得
        $mstAdventBattleRewardGroups = $this->getRewardGroups(
            $mstAdventBattleId
        )->filter(function ($entity) use ($raidTotalScore) {
            /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity $entity */
            return ((int) $entity->getConditionValue()) <= $raidTotalScore;
        })->sortBy(function ($entity) {
            return (int) $entity->getConditionValue();
        })->values();
        if ($mstAdventBattleRewardGroups->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // 取得済み報酬を除外
        $unreceivedRewardGroupIds = $this->getUnreceivedRewardGroupIds(
            $usrAdventBattle->getReceivedRaidRewardGroupId(),
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
        $usrAdventBattle->setReceivedRaidRewardGroupId($mstAdventBattleRewardGroupId);
        $this->usrAdventBattleRepository->syncModel($usrAdventBattle);
    }
}
