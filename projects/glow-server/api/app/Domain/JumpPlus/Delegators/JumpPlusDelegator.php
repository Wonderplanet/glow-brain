<?php

declare(strict_types=1);

namespace App\Domain\JumpPlus\Delegators;

use App\Domain\JumpPlus\Services\JumpPlusRewardService;
use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;
use App\Domain\Resource\Entities\JumpPlusRewardBundle;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class JumpPlusDelegator
{
    public function __construct(
        private JumpPlusRewardService $jumpPlusRewardService,
    ) {
    }

    /**
     * @param string $usrUserId
     * @param CarbonImmutable $now
     * @return Collection<JumpPlusRewardBundle>
     */
    public function getReceivableRewards(string $usrUserId, CarbonImmutable $now): Collection
    {
        return $this->jumpPlusRewardService->getReceivableRewards($usrUserId, $now);
    }

    /**
     * @param string $usrUserId
     * @param Collection<DynJumpPlusRewardEntity> $dynJumpPlusRewards
     */
    public function markRewardsAsReceived(string $usrUserId, Collection $dynJumpPlusRewards): void
    {
        $this->jumpPlusRewardService->markRewardsAsReceived($usrUserId, $dynJumpPlusRewards);
    }
}
