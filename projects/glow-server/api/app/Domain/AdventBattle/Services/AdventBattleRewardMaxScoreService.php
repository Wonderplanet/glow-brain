<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\AdventBattle\Entities\AdventBattleReceivableReward;
use App\Domain\AdventBattle\Enums\AdventBattleRewardCategory;
use App\Domain\AdventBattle\Models\UsrAdventBattleInterface;
use App\Domain\Resource\Entities\Rewards\AdventBattleMaxScoreReward;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardGroupRepository;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleRewardRepository;

class AdventBattleRewardMaxScoreService extends AdventBattleRewardService
{
    public function __construct(
        MstAdventBattleRewardRepository $mstAdventBattleRewardRepository,
        MstAdventBattleRewardGroupRepository $mstAdventBattleRewardGroupRepository,
    ) {
        parent::__construct(
            $mstAdventBattleRewardRepository,
            $mstAdventBattleRewardGroupRepository,
            AdventBattleRewardCategory::MAX_SCORE,
            AdventBattleMaxScoreReward::class
        );
    }

    /**
     * @inheritdoc
     */
    public function fetchAvailableRewards(UsrAdventBattleInterface $usrAdventBattle): AdventBattleReceivableReward
    {
        if ($usrAdventBattle->getMaxScore() === $usrAdventBattle->getMaxReceivedMaxScoreReward()) {
            // 受け取り可能分はすべて受け取っている
            return $this->createAdventBattleReceivableReward(collect());
        }

        $mstAdventBattleId = $usrAdventBattle->getMstAdventBattleId();

        // 最大スコアを満たしている報酬を全て取得
        $mstAdventBattleRewardGroups = $this->getRewardGroups(
            $mstAdventBattleId
        )->filter(function ($entity) use ($usrAdventBattle) {
            /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleRewardGroupEntity $entity */
            $value = (int) $entity->getConditionValue();
            // 受け取り済みの最大スコアより大きく未受取の最大スコア以下の報酬
            return $usrAdventBattle->getMaxReceivedMaxScoreReward() < $value
                && $value <= $usrAdventBattle->getMaxScore();
        });
        if ($mstAdventBattleRewardGroups->isEmpty()) {
            return $this->createAdventBattleReceivableReward(collect());
        }

        // 報酬グループから報酬を取得して返す
        return $this->createAdventBattleReceivableReward(
            $mstAdventBattleRewardGroups->map(fn($entity) => $entity->getId())
        );
    }
}
