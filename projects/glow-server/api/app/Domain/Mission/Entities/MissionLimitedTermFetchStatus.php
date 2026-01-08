<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

/**
 * UsrMissionLimitedTerm対象のミッション進捗データを格納するデータクラス
 */
class MissionLimitedTermFetchStatus
{
    public function __construct(
        private MissionFetchStatus $adventBattleMissionFetchStatus,
    ) {
    }

    public function getAdventBattleMissionFetchStatus(): MissionFetchStatus
    {
        return $this->adventBattleMissionFetchStatus;
    }
}
