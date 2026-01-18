<?php

namespace App\Services\Reward;
use App\Constants\RewardType;
use App\Dtos\RewardDto;
use App\Entities\RewardInfo;
use Illuminate\Support\Collection;

class RewardInfoGetHandleService
{
    private Collection $rewardInfos;

    private Collection $rewardInfoGetServiceClasses;

    public function __construct(
        private RewardCoinInfoGetService $rewardCoinInfoGetService,
        private RewardFreeDiamondInfoGetService $rewardFreeDiamondInfoGetService,
        private RewardDiamondInfoGetService $rewardDiamondInfoGetService,
        private RewardStaminaInfoGetService $rewardStaminaInfoGetService,
        private RewardItemInfoGetService $rewardItemInfoGetService,
        private RewardExpInfoGetService $rewardExpInfoGetService,
        private RewardIdleCoinInfoGetService $rewardIdleCoinInfoGetService,
        private RewardEmblemInfoGetService $rewardEmblemInfoGetService,
        private RewardUnitInfoGetService $rewardUnitInfoGetService,
        private RewardPaidDiamondInfoGetService $rewardPaidDiamondInfoGetService,
        private RewardAdInfoGetService $rewardAdInfoGetService,
        private RewardFreeInfoGetService $rewardFreeInfoGetService,
    ) {
        $this->rewardInfoGetServiceClasses = collect([
            RewardType::COIN->value => $this->rewardCoinInfoGetService,
            RewardType::FREE_DIAMOND->value => $this->rewardFreeDiamondInfoGetService,
            RewardType::DIAMOND->value => $this->rewardDiamondInfoGetService,
            RewardType::STAMINA->value => $this->rewardStaminaInfoGetService,
            RewardType::ITEM->value => $this->rewardItemInfoGetService,
            RewardType::EXP->value => $this->rewardExpInfoGetService,
            RewardType::IDLE_COIN->value => $this->rewardIdleCoinInfoGetService,
            RewardType::EMBLEM->value => $this->rewardEmblemInfoGetService,
            RewardType::UNIT->value => $this->rewardUnitInfoGetService,
            RewardType::PAID_DIAMOND->value => $this->rewardPaidDiamondInfoGetService,
            RewardType::AD->value => $this->rewardAdInfoGetService,
            RewardType::FREE->value => $this->rewardFreeInfoGetService,
        ]);
    }

    /**
     * @param Collection<RewardDto> $rewardDtos
     */
    public function build(Collection $rewardDtos): self
    {
        $this->createRewardInfos($rewardDtos);

        return $this;
    }

    private function createRewardInfos(Collection $rewardDtos): void
    {
        $typeGrouped = collect($rewardDtos)
            ->groupBy(function (RewardDto $rewardDto) {
                return $rewardDto->getRewardType();
            });

        $rewardInfos = collect();
        foreach ($typeGrouped as $rewardType => $rewardDtos) {
            /** @var Collection $rewardDtos */
            $rewardInfoGetService = $this->rewardInfoGetServiceClasses->get($rewardType);
            if ($rewardInfoGetService === null) {
                continue;
            }
            /** @var BaseRewardInfoGetService $rewardInfoGetService */

            $rewardInfos = $rewardInfos->merge(
                $rewardInfoGetService->build($rewardDtos)->getRewardInfos()
            );
        }
        $this->rewardInfos = $rewardInfos;
    }

    /**
     * @return Collection<string, RewardInfo>
     * key: id, value: RewardInfo
     */
    public function getRewardInfos(): Collection
    {
        return $this->rewardInfos->keyBy(function (RewardInfo $rewardInfo) {
            return $rewardInfo->getId();
        });
    }
}
