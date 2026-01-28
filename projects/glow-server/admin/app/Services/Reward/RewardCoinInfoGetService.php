<?php

namespace App\Services\Reward;

use App\Constants\RewardType;
use App\Entities\RewardInfo;
use App\Utils\AssetUtil;
use Illuminate\Support\Collection;

class RewardCoinInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::COIN;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    RewardType::COIN->label(),
                    null,
                    $rewardDto->getAmount(),
                    null,
                    $this->rewardType->value,
                    AssetUtil::makeCoinIconPath(),
                    AssetUtil::makeCoinBgPath(),
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
