<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Http\Responses\Data\UsrMissionBonusPointData;
use Illuminate\Support\Collection;

class MissionFetchStatus
{
    /**
     * @param Collection<\App\Http\Responses\Data\UsrMissionStatusData> $usrMissionStatusDataList
     */
    public function __construct(
        private Collection $usrMissionStatusDataList,
        private ?UsrMissionBonusPointData $usrMissionBonusPointData,
    ) {
        $this->usrMissionStatusDataList = $usrMissionStatusDataList;
        $this->usrMissionBonusPointData = $usrMissionBonusPointData;
    }

    public function getUsrMissionStatusDataList(): Collection
    {
        return $this->usrMissionStatusDataList;
    }

    public function getUsrMissionBonusPointData(): ?UsrMissionBonusPointData
    {
        return $this->usrMissionBonusPointData;
    }
}
