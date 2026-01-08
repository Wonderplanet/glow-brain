<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Services;

use App\Domain\Common\Factories\LotteryFactory;
use App\Domain\Gacha\Entities\NoPrizeContent;
use App\Domain\Resource\Entities\Rewards\AdventBattleAlwaysClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleFirstClearReward;
use App\Domain\Resource\Entities\Rewards\AdventBattleRandomClearReward;
use App\Domain\Resource\Mst\Entities\MstAdventBattleClearRewardEntity;
use App\Domain\Resource\Mst\Repositories\MstAdventBattleClearRewardRepository;
use Illuminate\Support\Collection;

class AdventBattleClearRewardService
{
    public function __construct(
        private MstAdventBattleClearRewardRepository $mstAdventBattleClearRewardRepository,
        private LotteryFactory $lotteryFactory,
    ) {
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    public function getFirstClearRewards(
        string $mstAdventBattleId,
    ): Collection {
        $firstClearRewards = $this->mstAdventBattleClearRewardRepository->getFirstClearRewardsByMstAdventBattleId(
            $mstAdventBattleId
        );
        return $this->createFirstClearRewards($firstClearRewards);
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    public function getAlwaysClearRewards(
        string $mstAdventBattleId,
    ): Collection {
        $clearRewards = $this->mstAdventBattleClearRewardRepository->getAlwaysRewardsByMstAdventBattleId(
            $mstAdventBattleId
        );
        return $this->createAlwaysClearRewards($clearRewards);
    }

    /**
     * @param string $mstAdventBattleId
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    public function getRandomClearRewards(
        string $mstAdventBattleId,
    ): Collection {
        $clearRewards = $this->mstAdventBattleClearRewardRepository->getRandomRewardsByMstAdventBattleId(
            $mstAdventBattleId
        );
        $lotteryClearRewards = $this->getLotteryPercentageAdventBattleReward($clearRewards);
        return $this->createRandomClearRewards($lotteryClearRewards);
    }

    /**
     * @param Collection<MstAdventBattleClearRewardEntity> $lotteryClearRewards
     * @return Collection<MstAdventBattleClearRewardEntity>
     */
    private function getLotteryPercentageAdventBattleReward(Collection $lotteryClearRewards): Collection
    {
        $result = collect();
        if ($lotteryClearRewards->isEmpty()) {
            return $result;
        }
        /** @var \App\Domain\Resource\Mst\Entities\MstAdventBattleClearRewardEntity $lotteryClearReward */
        foreach ($lotteryClearRewards as $lotteryClearReward) {
            $dropPercentage = min(100, $lotteryClearReward->getPercentage());
            $lottery = $this->lotteryFactory->createFromMapWithNoPrize(
                weightMap: collect([$lotteryClearReward->getId() => $dropPercentage]),
                contentMap: collect([$lotteryClearReward->getId() => $lotteryClearReward]),
                noPrizeWeight: 100 - $dropPercentage
            );
            $drawResult = $lottery->draw();
            if (!($drawResult instanceof NoPrizeContent)) {
                $result->push($drawResult);
            }
        }
        return $result;
    }

    /**
     * @param Collection<MstAdventBattleClearRewardEntity> $firstClearRewards
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function createFirstClearRewards(
        Collection $firstClearRewards,
    ): Collection {
        $sendRewards = collect();
        foreach ($firstClearRewards as $firstClearReward) {
            $sendRewards->push(
                new AdventBattleFirstClearReward(
                    $firstClearReward->getResourceType(),
                    $firstClearReward->getResourceId(),
                    $firstClearReward->getResourceAmount(),
                    $firstClearReward->getMstAdventBattleId(),
                ),
            );
        }
        return $sendRewards;
    }

    /**
     * @param Collection<MstAdventBattleClearRewardEntity> $clearRewards
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function createAlwaysClearRewards(
        Collection $clearRewards,
    ): Collection {
        $sendRewards = collect();
        foreach ($clearRewards as $clearReward) {
            $sendRewards->push(
                new AdventBattleAlwaysClearReward(
                    $clearReward->getResourceType(),
                    $clearReward->getResourceId(),
                    $clearReward->getResourceAmount(),
                    $clearReward->getMstAdventBattleId(),
                ),
            );
        }
        return $sendRewards;
    }

    /**
     * @param Collection<MstAdventBattleClearRewardEntity> $clearRewards
     * @return Collection<\App\Domain\Resource\Entities\Rewards\BaseReward>
     */
    protected function createRandomClearRewards(
        Collection $clearRewards,
    ): Collection {
        $sendRewards = collect();
        foreach ($clearRewards as $clearReward) {
            $sendRewards->push(
                new AdventBattleRandomClearReward(
                    $clearReward->getResourceType(),
                    $clearReward->getResourceId(),
                    $clearReward->getResourceAmount(),
                    $clearReward->getMstAdventBattleId(),
                ),
            );
        }
        return $sendRewards;
    }
}
