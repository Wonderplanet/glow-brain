<?php

declare(strict_types=1);

namespace App\Entities;

use App\Domain\Resource\Dtos\LogTriggerDto;
use App\Domain\Resource\Dtos\RewardDto;
use App\Domain\Resource\Entities\Rewards\BaseReward as ApiBaseReward;

class Reward extends ApiBaseReward
{
    public static function createByArray(array $rewardArray): self
    {
        $triggerSource = $rewardArray['triggerSource'] ?? '';
        $triggerValue = $rewardArray['triggerValue'] ?? '';
        $triggerOption = $rewardArray['triggerOption'] ?? '';
        $logTriggerData = new LogTriggerDto($triggerSource, $triggerValue, $triggerOption);

        $rewardDto = self::createRewardDtoByArray($rewardArray);
        $beforeRewardDto = $rewardArray['preConversionResource'] ?? null;
        if ($beforeRewardDto !== null) {
            $beforeRewardDto = self::createRewardDtoByArray($beforeRewardDto);
        }

        /**
         * @var RewardDto $originalRewardDto
         */
        $originalRewardDto = $beforeRewardDto ?? $rewardDto;

        $reward = new self(
            $originalRewardDto->getType(),
            $originalRewardDto->getResourceId(),
            $originalRewardDto->getAmount(),
            $logTriggerData,
        );

        $reward->setRewardData($rewardDto);

        return $reward;
    }

    private static function createRewardDtoByArray(array $resourceArray): RewardDto
    {
        $resourceType = $resourceArray['resourceType'] ?? '';
        $resourceId = $resourceArray['resourceId'] ?? '';
        $resourceAmount = $resourceArray['resourceAmount'] ?? 0;

        return new RewardDto($resourceType, $resourceId, $resourceAmount);
    }
}
