<?php

declare(strict_types=1);

namespace App\Domain\Reward\Entities;

use App\Domain\Resource\Entities\Rewards\BaseReward;
use Illuminate\Support\Collection;

class RewardSent
{
    /** @var Collection<BaseReward> $rewards */
    private Collection $rewards;

    public function __construct(
        Collection $rewards,
    ) {
        $this->rewards = $rewards;
    }

    /**
     * @return Collection<BaseReward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }
}
