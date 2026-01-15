<?php

declare(strict_types=1);

namespace App\Http\Responses\ResultData;

use Illuminate\Support\Collection;

class MissionAdventBattleFetchResultData
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionEventStatusDataList
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionLimitedTermStatusDataList
     */
    public function __construct(
        public Collection $usrMissionEventStatusDataList,
        public Collection $usrMissionLimitedTermStatusDataList,
    ) {
    }
}
