<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use Illuminate\Support\Collection;

/**
 * UsrMissionLimitedTerm対象のミッション進捗データを格納するデータクラス
 */
class MissionLimitedTermFetchStatus
{
    public function __construct(
        private MissionFetchStatus $adventBattleMissionFetchStatus,
        private MissionFetchStatus $artworkPanelMissionFetchStatus,
    ) {
    }

    public function getAdventBattleMissionFetchStatus(): MissionFetchStatus
    {
        return $this->adventBattleMissionFetchStatus;
    }

    public function getArtworkPanelMissionFetchStatus(): MissionFetchStatus
    {
        return $this->artworkPanelMissionFetchStatus;
    }

    public function getUsrMissionStatusDataList(): Collection
    {
        return $this->adventBattleMissionFetchStatus->getUsrMissionStatusDataList()
            ->merge(
                $this->artworkPanelMissionFetchStatus->getUsrMissionStatusDataList(),
            );
    }
}
