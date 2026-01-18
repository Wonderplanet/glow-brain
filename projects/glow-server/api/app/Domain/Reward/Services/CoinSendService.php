<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;
use App\Domain\User\Delegators\UserDelegator;

class CoinSendService implements RewardSendServiceInterface
{
    public function __construct(
        private UserDelegator $userDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        $this->userDelegator->addCoinByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent($rewards);
    }
}
