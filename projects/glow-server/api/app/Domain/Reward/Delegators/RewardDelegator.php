<?php

declare(strict_types=1);

namespace App\Domain\Reward\Delegators;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Resource\Entities\RewardSendPolicy;
use App\Domain\Reward\Managers\RewardManager;
use App\Domain\Reward\Services\RewardSendService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class RewardDelegator
{
    public function __construct(
        private RewardManager $rewardManager,
        private RewardSendService $rewardSendService,
    ) {
    }

    public function addReward(BaseReward $reward): void
    {
        $this->rewardManager->addReward($reward);
    }

    public function addRewards(Collection $rewards): void
    {
        $this->rewardManager->addRewards($rewards);
    }

    public function sendRewards(
        string $usrUserId,
        int $platform,
        CarbonImmutable $now,
        ?RewardSendPolicy $policy = null,
    ): void {
        $this->rewardSendService->sendRewards($usrUserId, $platform, $now, $policy);
    }

    public function getSentRewards(string $rewardClass): Collection
    {
        return $this->rewardManager->getSentRewards($rewardClass);
    }

    public function getConvertedRewardsWithoutSend(
        string $usrUserId,
        CarbonImmutable $now,
        Collection $rewards,
    ): Collection {
        return $this->rewardSendService->getConvertedRewardsWithoutSend($usrUserId, $now, $rewards);
    }
}
