<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use App\Domain\Resource\Dyn\Entities\DynJumpPlusRewardEntity;
use App\Domain\Resource\Entities\Rewards\JumpPlusReward;
use Illuminate\Support\Collection;

/**
 * 同種の報酬関連のEntityを束ねるクラス
 */
class JumpPlusRewardBundle
{
    /**
     * @param Collection<JumpPlusReward> $jumpPlusRewards
     */
    public function __construct(
        private DynJumpPlusRewardEntity $dynJumpPlusReward,
        private Collection $jumpPlusRewards,
    ) {
    }

    public function getDynJumpPlusReward(): DynJumpPlusRewardEntity
    {
        return $this->dynJumpPlusReward;
    }

    /**
     * @return Collection<JumpPlusReward>
     */
    public function getJumpPlusRewards(): Collection
    {
        return $this->jumpPlusRewards;
    }
}
