<?php

namespace App\Services\Reward;

use App\Constants\RewardType;

class RewardIdleCoinInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::IDLE_COIN;
}
