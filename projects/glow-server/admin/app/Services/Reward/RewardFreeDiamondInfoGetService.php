<?php

namespace App\Services\Reward;

use App\Constants\RewardType;
use App\Entities\RewardInfo;
use Illuminate\Support\Collection;
use App\Utils\AssetUtil;

class RewardFreeDiamondInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::FREE_DIAMOND;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    RewardType::DIAMOND->label(),
                    null,
                    $rewardDto->getAmount(),
                    null,
                    $this->rewardType->value,
                    AssetUtil::makeDiamondIconPath(),
                    AssetUtil::makeDiamondBgPath(),
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
