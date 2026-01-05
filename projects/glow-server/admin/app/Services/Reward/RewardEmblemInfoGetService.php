<?php

namespace App\Services\Reward;
use App\Constants\RewardType;
use App\Dtos\RewardDto;
use App\Entities\RewardInfo;
use App\Filament\Pages\EmblemDetail;
use App\Models\Mst\MstEmblem;
use Illuminate\Support\Collection;
use App\Constants\ImagePath;
use App\Constants\RarityType;
use App\Utils\AssetUtil;

class RewardEmblemInfoGetService extends BaseRewardInfoGetService
{
    protected ?RewardType $rewardType = RewardType::EMBLEM;

    protected function createRewardInfos(Collection $rewardDtos): void
    {
        $mstEmblemIds = $rewardDtos
            ->map(function (RewardDto $rewardDto) {
                return $rewardDto->getResourceId();
            })
            ->unique();
        if ($mstEmblemIds->isEmpty()) {
            return;
        }
        $mstEmblems = MstEmblem::query()
            ->with('mst_emblem_i18n')
            ->whereIn('mst_emblems.id', $mstEmblemIds)
            ->get()
            ->keyBy(function (MstEmblem $mstEmblem) {
                return $mstEmblem->id;
            });

        $rewardInfos = collect();
        foreach ($rewardDtos as $rewardDto) {
            if ($this->isValidRewardDto($rewardDto) === false) {
                continue;
            }

            $mstEmblem = $mstEmblems->get($rewardDto->getResourceId());
            if ($mstEmblem === null) {
                continue;
            }

            $rewardInfos->push(
                new RewardInfo(
                    $rewardDto->getId(),
                    $mstEmblem->mst_emblem_i18n->name,
                    $mstEmblem->id,
                    $rewardDto->getAmount(),
                    EmblemDetail::getUrl(['mstEmblemId' => $mstEmblem->id]),
                    $this->rewardType->value,
                    $mstEmblem->makeAssetPath(),
                    $mstEmblem->makeBgPath(),
                )
            );
        }

        $this->rewardInfos = $rewardInfos;
    }
}
