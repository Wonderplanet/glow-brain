<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Emblem\Delegators\EmblemDelegator;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;

class EmblemSendService implements RewardSendServiceInterface
{
    public function __construct(
        private EmblemDelegator $emblemDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // aggregate
        $newMstEmblemIds = collect();
        foreach ($rewards as $reward) {
            /** @var \App\Domain\Resource\Entities\Rewards\BaseReward $reward */
            $newMstEmblemIds->push($reward->getResourceId());
            $reward->markAsSent();
        }

        // send
        $this->emblemDelegator->addUsrEmblems($usrUserId, $newMstEmblemIds);

        return new RewardSent($rewards);
    }
}
