<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class EncyclopediaReceiveRewardResultData
{
    /**
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrReceivedUnitEncyclopediaReward> $usrReceivedUnitEncyclopediaRewards @codingStandardsIgnoreLine
     * @param Collection<\App\Domain\Resource\Entities\Rewards\UnitEncyclopediaReward> $unitEncyclopediaRewards
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> $usrConditionPacks
     */
    public function __construct(
        public Collection $usrReceivedUnitEncyclopediaRewards,
        public Collection $unitEncyclopediaRewards,
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrConditionPacks,
    ) {
    }
}
