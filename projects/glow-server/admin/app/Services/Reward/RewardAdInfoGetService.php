<?php

namespace App\Services\Reward;

use App\Constants\RewardType;

class RewardAdInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::AD;
}
