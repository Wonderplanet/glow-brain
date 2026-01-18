<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

/**
 * ミッションの報酬未受け取り数情報を格納するデータクラス
 */
class MissionUnreceivedReward
{
    public function __construct(
        private int $achievementCount,
        private int $dailyCount,
        private int $weeklyCount,
        private int $beginnerCount,
    ) {
        $this->achievementCount = $achievementCount;
        $this->dailyCount = $dailyCount;
        $this->weeklyCount = $weeklyCount;
        $this->beginnerCount = $beginnerCount;
    }

    public function getAchievementCount(): int
    {
        return $this->achievementCount;
    }

    public function getDailyCount(): int
    {
        return $this->dailyCount;
    }

    public function getWeeklyCount(): int
    {
        return $this->weeklyCount;
    }

    public function getBeginnerCount(): int
    {
        return $this->beginnerCount;
    }

    /**
     * 初心者ミッション以外のミッション報酬未受け取り数の合算値を返す
     * @return int
     */
    public function getUnreceivedMissionRewardCount(): int
    {
        return $this->achievementCount + $this->dailyCount + $this->weeklyCount;
    }

    /**
     * 初心者ミッションの報酬未受け取り数を返す
     * @return int
     */
    public function getUnreceivedMissionBeginnerRewardCount(): int
    {
        return $this->beginnerCount;
    }
}
