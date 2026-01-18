<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

class UserLevelUpData
{
    /**
     * @param int $beforeExp
     * @param int $afterExp
     * @param Collection<\App\Domain\Resource\Entities\Rewards\UserLevelUpReward> $levelUpRewards
     */
    public function __construct(
        public int $beforeExp,
        public int $afterExp,
        public Collection $levelUpRewards,
    ) {
    }
}
