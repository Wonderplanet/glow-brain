<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MissionEventUpdateAndFetchResultData
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionEventStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionEventDailyStatusDataList
     */
    public function __construct(
        public Collection $usrMissionEventStatusDataList,
        public Collection $usrMissionEventDailyStatusDataList,
    ) {
    }
}
