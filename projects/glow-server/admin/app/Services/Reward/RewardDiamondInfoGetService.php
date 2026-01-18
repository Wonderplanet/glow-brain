<?php

namespace App\Services\Reward;

use App\Constants\RewardType;

class RewardDiamondInfoGetService extends RewardFreeDiamondInfoGetService
{
    protected ?RewardType $rewardType = RewardType::DIAMOND;
}
