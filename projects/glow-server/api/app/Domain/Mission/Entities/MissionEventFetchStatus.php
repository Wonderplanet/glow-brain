<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

/**
 * UsrMissionEvent対象のミッション進捗データを格納するデータクラス
 */
class MissionEventFetchStatus
{
    public function __construct(
        private MissionFetchStatus $eventMissionFetchStatusData,
        private MissionFetchStatus $eventDailyMissionFetchStatusData,
    ) {
    }

    public function getEventMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->eventMissionFetchStatusData;
    }

    public function getEventDailyMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->eventDailyMissionFetchStatusData;
    }
}
