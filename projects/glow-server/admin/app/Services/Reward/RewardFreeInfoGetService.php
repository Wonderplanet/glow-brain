<?php

namespace App\Services\Reward;

use App\Constants\RewardType;

class RewardFreeInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::FREE;
}
