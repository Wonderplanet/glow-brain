<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use App\Http\Responses\Data\UserLevelUpData;
use App\Http\Responses\Data\UsrParameterData;
use Illuminate\Support\Collection;

class MissionBulkReceiveRewardResultData
{
    /**
     * @param Collection<\App\Domain\Mission\Entities\MissionReceiveRewardStatus> $missionReceiveRewardStatuses
     * @param Collection<\App\Domain\Resource\Entities\Rewards\MissionReward> $missionRewards
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionAchievementStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionDailyStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionWeeklyStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionBeginnerStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionEventStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionEventDailyStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionlimitedTermStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionBonusPointData> $usrMissionBonusPoints
     * @param Collection<\App\Domain\Item\Models\UsrItemInterface>  $usrItems
     * @param Collection<\App\Domain\Unit\Models\UsrUnitInterface>  $usrUnits
     * @param Collection<\App\Domain\Shop\Models\UsrConditionPackInterface> $usrConditionPacks
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrArtworkInterface> $usrArtworks
     * @param Collection<\App\Domain\Encyclopedia\Models\UsrArtworkFragmentInterface> $usrArtworkFragments
     */
    public function __construct(
        public Collection $missionReceiveRewardStatuses,
        public Collection $missionRewards,
        // ミッション進捗
        public Collection $usrMissionAchievementStatusDataList,
        public Collection $usrMissionDailyStatusDataList,
        public Collection $usrMissionWeeklyStatusDataList,
        public Collection $usrMissionBeginnerStatusDataList,
        public Collection $usrMissionEventStatusDataList,
        public Collection $usrMissionEventDailyStatusDataList,
        public Collection $usrMissionlimitedTermStatusDataList,
        public Collection $usrMissionBonusPoints,
        // ミッション以外
        public UsrParameterData $usrUserParameter,
        public Collection $usrItems,
        public Collection $usrUnits,
        public UserLevelUpData $userLevelUpData,
        public Collection $usrConditionPacks,
        public Collection $usrArtworks,
        public Collection $usrArtworkFragments,
    ) {
    }
}
