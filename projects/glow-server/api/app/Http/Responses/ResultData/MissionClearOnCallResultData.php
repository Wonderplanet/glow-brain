<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MissionClearOnCallResultData
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionAchievementStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionBeginnerStatusDataList
     */
    public function __construct(
        public Collection $usrMissionAchievementStatusDataList,
        public Collection $usrMissionBeginnerStatusDataList,
    ) {
    }
}
