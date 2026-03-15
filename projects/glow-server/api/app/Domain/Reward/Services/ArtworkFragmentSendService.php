<?php

declare(strict_types=1);

namespace App\Domain\Reward\Services;

use App\Domain\Encyclopedia\Delegators\EncyclopediaDelegator;
use App\Domain\Resource\Entities\Rewards\BaseReward;
use App\Domain\Reward\Entities\RewardSendContext;
use App\Domain\Reward\Entities\RewardSent;

class ArtworkFragmentSendService implements RewardSendServiceInterface
{
    public function __construct(
        private EncyclopediaDelegator $encyclopediaDelegator,
    ) {
    }

    /**
     * 原画のかけらは重複しても変換はせず破棄する
     */
    public function send(RewardSendContext $context): RewardSent
    {
        $usrUserId = $context->getUsrUserId();
        $rewards = $context->getRewards();

        $mstArtworkFragmentIds = collect();
        foreach ($rewards as $reward) {
            /** @var BaseReward $reward */
            $mstArtworkFragmentId = $reward->getResourceId();

            // 個数は考慮しない
            $mstArtworkFragmentIds->put(
                $mstArtworkFragmentId,
                $mstArtworkFragmentId,
            );

            $reward->markAsSent();
        }

        if ($mstArtworkFragmentIds->isNotEmpty()) {
            $this->encyclopediaDelegator->createUnownedUsrArtworkFragments(
                $usrUserId,
                $mstArtworkFragmentIds->keys(),
            );
        }

        return new RewardSent($rewards);
    }
}
