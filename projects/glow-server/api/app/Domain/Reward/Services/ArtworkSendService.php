<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;

class ArtworkSendService implements RewardSendServiceInterface
{
    public function __construct(
        private EncyclopediaDelegator $encyclopediaDelegator,
    ) {
    }

    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();

        // aggregate
        $newMstArtworkIds = collect();
        foreach ($rewards as $reward) {
            /** @var \App\Domain\Resource\Entities\Rewards\BaseReward $reward */
            $mstArtworkId = $reward->getResourceId();
            $newMstArtworkIds->push($mstArtworkId);
            $reward->markAsSent();
        }

        // send
        $this->encyclopediaDelegator->grantArtworksWithFragments($usrUserId, $newMstArtworkIds);

        return new RewardSent($rewards);
    }
}
