<?php

namespace App\Services\Reward;

use App\Constants\RewardType;

class RewardStaminaInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::STAMINA;
}
