<?php

namespace App\Services\Reward;
use App\Constants\RewardType;
use App\Dtos\RewardDto;
use App\Entities\RewardInfo;
use App\Filament\Pages\MstItemDetail;
use App\Models\Mst\MstItem;
use App\Utils\AssetUtil;
use Illuminate\Support\Collection;
use App\Constants\ImagePath;

class RewardItemInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::ITEM;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $mstItemIds = $rewardDtos
            ->map(function (RewardDto $rewardDto) {
                return $rewardDto->getResourceId();
            })
            ->unique();
        if ($mstItemIds->isEmpty()) {
            return;
        }
        $mstItems = MstItem::query()
            ->with('mst_item_i18n')
            ->whereIn('mst_items.id', $mstItemIds)
            ->get()
            ->keyBy(function (MstItem $mstItem) {
                return $mstItem->id;
            });

        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            if ($this->isValidRewardDto($rewardDto) === false) {
                continue;
            }

            $mstItem = $mstItems->get($rewardDto->getResourceId());
            if ($mstItem === null) {
                continue;
            }

            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    $mstItem->mst_item_i18n?->name ?? '',
                    $mstItem->id,
                    $rewardDto->getAmount(),
                    MstItemDetail::getUrl(['mstItemId' => $mstItem->id]),
                    $this->rewardType->value,
                    $mstItem->makeAssetPath(),
                    $mstItem->makeBgPath(),
                    $mstItem->rarity,
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
