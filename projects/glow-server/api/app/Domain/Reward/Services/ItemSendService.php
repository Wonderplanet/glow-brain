<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Item\Delegators\ItemDelegator;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;

class ItemSendService implements RewardSendServiceInterface
{
    public function __construct(
        private ItemDelegator $itemDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();
        $now = $context->getNow();

        // send
        $this->itemDelegator->addItemByRewards(
            usrUserId: $usrUserId,
            rewards: $rewards,
            now: $now,
        );

        return new RewardSent(
            rewards: $rewards,
        );
    }
}
