<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Domain\Emblem\Models\UsrEmblemInterface;
use App\Domain\IdleIncentive\Models\UsrIdleIncentiveInterface;
use App\Domain\Resource\Entities\Rewards\MissionDailyBonusReward;
use App\Domain\Resource\Entities\Rewards\MissionEventDailyBonusReward;
use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class TutorialUpdateStatusResultData
{
    /**
     * @param Collection<\App\Domain\Gacha\Models\UsrGachaInterface> $usrGachas
     * @param Collection<MissionDailyBonusReward> $missionDailyBonusRewards
     * @param Collection<MissionEventDailyBonusReward> $missionEventDailyBonusRewards
     * @param Collection<\App\Domain\Mission\Models\UsrMissionEventDailyBonusProgressInterface> $usrMissionEventDailyBonusProgresses
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface> $usrUnits
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface> $usrItems
     * @param Collection<UsrEmblemInterface> $usrEmblems
     * @param Collection<\App\Domain\Resource\Usr\Entities\UsrConditionPackEntity> $usrConditionPacks
     */
    public function __construct(
        public Collection $usrGachas,
        public ?UsrIdleIncentiveInterface $usrIdleIncentive,
        public Collection $missionDailyBonusRewards,
        public Collection $missionEventDailyBonusRewards,
        public Collection $usrMissionEventDailyBonusProgresses,
        public UsrParameterData $usrParameterData,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrUnits,
        public Collection $usrItems,
        public Collection $usrEmblems,
        public Collection $usrConditionPacks,
    ) {
    }
}
