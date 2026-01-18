<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MissionUpdateAndFetchResultData
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionAchievementStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionDailyStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionWeeklyStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionBeginnerStatusDataList
     * @param int $missionBeginnerDaysFromStart
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionDailyBonusStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionBonusPointData> $usrMissionBonusPoints
     */
    public function __construct(
        public Collection $usrMissionAchievementStatusDataList,
        public Collection $usrMissionDailyStatusDataList,
        public Collection $usrMissionWeeklyStatusDataList,
        public Collection $usrMissionBeginnerStatusDataList,
        public int $missionBeginnerDaysFromStart,
        public Collection $usrMissionDailyBonusStatusDataList,
        public Collection $usrMissionBonusPoints,
    ) {
    }
}
