<?php

namespace App\Services\Reward;
use App\Constants\RewardType;
use App\Dtos\RewardDto;
use App\Entities\RewardInfo;
use Illuminate\Support\Collection;

abstract class BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = null;

    protected Collection $rewardInfos;

    public function build(Collection $rewardDtos): self
    {
        $this->createRewardInfos($rewardDtos);

        return $this;
    }

    public function isValidRewardDto(RewardDto $rewardDto): bool
    {
        if ($this->rewardType === null) {
            return false;
        }

        return $rewardDto->getRewardType() === $this->rewardType->value;
    }

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            if ($this->isValidRewardDto($rewardDto) === false) {
                continue;
            }

            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    $this->rewardType->label(),
                    null,
                    $rewardDto->getAmount(),
                    null,
                    $this->rewardType->value,
                    null,
                    null,
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }

    public function getRewardInfos(): Collection
    {
        return $this->rewardInfos;
    }
}
