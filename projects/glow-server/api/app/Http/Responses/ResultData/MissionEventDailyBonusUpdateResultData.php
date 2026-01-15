<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class MissionEventDailyBonusUpdateResultData
{
    /**
     * @param Collection<\App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward>  $missionEventDailyBonusRewards
     * @param Collection<\App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface>  $usrMissionEventDailyBonusProgresses
     * @param Collection<\App\Domain\Emblem\Models\UsrEmblemInterface>  $usrEmblems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface>  $usrUnits
     */
    public function __construct(
        public Collection $missionEventDailyBonusRewards,
        public Collection $usrMissionEventDailyBonusProgresses,
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $usrUnits,
        public Collection $usrEmblems,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrConditionPacks,
    ) {
    }
}
