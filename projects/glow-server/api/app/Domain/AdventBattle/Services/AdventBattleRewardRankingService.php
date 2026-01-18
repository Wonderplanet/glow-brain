<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Constants\AdventBattleConstant;
use App\Domain\AdventBattle\Entities\AdventBattleReceivableReward;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardRepository;

class AdventBattleRewardRankingService extends AdventBattleRewardService
{
    public function __construct(
        MstAdventBattleRewardRepository $mstAdventBattleRewardRepository,
        MstAdventBattleRewardGroupRepository $mstAdventBattleRewardGroupRepository,
        private readonly AdventBattleCacheService $adventBattleCacheService,
    ) {
        parent::__construct(
            $mstAdventBattleRewardRepository,
            $mstAdventBattleRewardGroupRepository,
            AdventBattleRewardCategory::RANKING,
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

        $adventBattleMyRankingData = $this
            ->adventBattleCacheService
            ->generateAdventBattleMyRankingData($usrAdventBattle);
        $rank = $adventBattleMyRankingData->getRank();

        $mstAdventBattleRewardGroups = $this->getRewardGroups($mstAdventBattleId);
        $participationRewardGroups = collect();
        $filteredRewardGroups = collect();

        foreach ($mstAdventBattleRewardGroups as $entity) {
            if ($entity->getConditionValue() === AdventBattleConstant::RANKING_REWARD_PARTICIPATION) {
                $participationRewardGroups->push($entity);
            } else {
                $filteredRewardGroups->push($entity);
            }
        }

        // ランク条件を満たしている報酬を全て取得
        $rankRewardGroup = $filteredRewardGroups->filter(
            fn($entity) => (int) $entity->getConditionValue() >= $rank
        )->sortBy(function ($entity) {
            return (int) $entity->getConditionValue();
        })->first();

        if (is_null($rankRewardGroup) && $participationRewardGroups->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // ランク報酬or参加賞のIDを取得
        if (is_null($rankRewardGroup)) {
            $rewardGroupIds = $participationRewardGroups->map(fn($entity) => $entity->getId());
        } else {
            $rewardGroupIds = collect([$rankRewardGroup->getId()]);
        }

        // 報酬グループから報酬を取得して返す
        return $this->createAdventBattleReceivableReward($rewardGroupIds);
    }
}
