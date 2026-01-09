<?php

namespace App\Services\Reward;

use App\Constants\RewardType;
use App\Entities\RewardInfo;
use App\Utils\AssetUtil;
use Illuminate\Support\Collection;

class RewardPaidDiamondInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::PAID_DIAMOND;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    RewardType::PAID_DIAMOND->label(),
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
