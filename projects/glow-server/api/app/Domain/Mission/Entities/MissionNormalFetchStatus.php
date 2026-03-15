<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

/**
 * UsrMissionNormal対象のミッション進捗データを格納するデータクラス
 */
class MissionNormalFetchStatus
{
    public function __construct(
        private MissionFetchStatus $achievementMissionFetchStatusData,
        private MissionFetchStatus $dailyMissionFetchStatusData,
        private MissionFetchStatus $weeklyMissionFetchStatusData,
        private MissionFetchStatus $beginnerMissionFetchStatusData,
    ) {
        $this->achievementMissionFetchStatusData = $achievementMissionFetchStatusData;
        $this->dailyMissionFetchStatusData = $dailyMissionFetchStatusData;
        $this->weeklyMissionFetchStatusData = $weeklyMissionFetchStatusData;
        $this->beginnerMissionFetchStatusData = $beginnerMissionFetchStatusData;
    }

    public function getAchievementMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->achievementMissionFetchStatusData;
    }

    public function getDailyMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->dailyMissionFetchStatusData;
    }

    public function getWeeklyMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->weeklyMissionFetchStatusData;
    }

    public function getBeginnerMissionFetchStatusData(): MissionFetchStatus
    {
        return $this->beginnerMissionFetchStatusData;
    }
}
