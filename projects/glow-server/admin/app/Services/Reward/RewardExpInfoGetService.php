<?php

namespace App\Services\Reward;

use App\Constants\RewardType;
use App\Entities\RewardInfo;
use App\Utils\AssetUtil;
use Illuminate\Support\Collection;

class RewardExpInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::EXP;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    RewardType::EXP->label(),
                    null,
                    $rewardDto->getAmount(),
                    null,
                    $this->rewardType->value,
                    AssetUtil::makeExpIconPath(),
                    AssetUtil::makeExpBgPath(),
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
