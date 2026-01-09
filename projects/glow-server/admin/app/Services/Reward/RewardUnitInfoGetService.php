<?php

namespace App\Services\Reward;
use App\Constants\RewardType;
use App\Dtos\RewardDto;
use App\Entities\RewardInfo;
use App\Filament\Pages\MstUnitDetail;
use App\Models\Mst\MstUnit;
use App\Utils\AssetUtil;
use Illuminate\Support\Collection;
use App\Constants\ImagePath;

class RewardUnitInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::UNIT;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $mstUnitIds = $rewardDtos
            ->map(function (RewardDto $rewardDto) {
                return $rewardDto->getResourceId();
            })
            ->unique();
        if ($mstUnitIds->isEmpty()) {
            return;
        }
        $mstUnits = MstUnit::query()
            ->with('mst_unit_i18n')
            ->whereIn('mst_units.id', $mstUnitIds)
            ->get()
            ->keyBy(function (MstUnit $mstUnit) {
                return $mstUnit->id;
            });

        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            if ($this->isValidRewardDto($rewardDto) === false) {
                continue;
            }

            $mstUnit = $mstUnits->get($rewardDto->getResourceId());
            if ($mstUnit === null) {
                continue;
            }

            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    $mstUnit?->mst_unit_i18n?->name ?? '',
                    $mstUnit->id,
                    $rewardDto->getAmount(),
                    MstUnitDetail::getUrl(['mstUnitId' => $mstUnit->id]),
                    $this->rewardType->value,
                    $mstUnit->makeAssetPath(),
                    $mstUnit->makeBgPath(),
                    $mstUnit->rarity,
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
