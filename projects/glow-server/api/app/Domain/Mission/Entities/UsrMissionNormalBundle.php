<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionNormalInterface;
use Illuminate\Support\Collection;

/**
 * usr_mission_normalsテーブルのユーザーデータをミッションタイプごとに整理してまとめたクラス
 */
class UsrMissionNormalBundle
{
    /**
     * @param Collection<string, UsrMissionNormalInterface> $achievements key: mst_mission_achievement_id
     * @param Collection<string, UsrMissionNormalInterface> $beginners key: mst_mission_beginner_id
     * @param Collection<string, UsrMissionNormalInterface> $dailies key: mst_mission_daily_id
     * @param Collection<string, UsrMissionNormalInterface> $weeklies key: mst_mission_weekly_id
     */
    public function __construct(
        private Collection $achievements,
        private Collection $beginners,
        private Collection $dailies,
        private Collection $weeklies,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->achievements->isEmpty()
            && $this->beginners->isEmpty()
            && $this->dailies->isEmpty()
            && $this->weeklies->isEmpty();
    }

    /**
     * @return Collection<string, UsrMissionNormalInterface> key: mst_mission_achievement_id
     */
    public function getAchievements(): Collection
    {
        return $this->achievements;
    }

    /**
     * @return Collection<string, UsrMissionNormalInterface> key: mst_mission_beginner_id
     */
    public function getBeginners(): Collection
    {
        return $this->beginners;
    }

    /**
     * @return Collection<string, UsrMissionNormalInterface> key: mst_mission_daily_id
     */
    public function getDailies(): Collection
    {
        return $this->dailies;
    }

    /**
     * @return Collection<string, UsrMissionNormalInterface> key: mst_mission_weekly_id
     */
    public function getWeeklies(): Collection
    {
        return $this->weeklies;
    }

    public function getMstMissionAchievementIds(): Collection
    {
        return $this->achievements->keys();
    }

    public function getMstMissionBeginnerIds(): Collection
    {
        return $this->beginners->keys();
    }

    public function getMstMissionDailyIds(): Collection
    {
        return $this->dailies->keys();
    }

    public function getMstMissionWeeklyIds(): Collection
    {
        return $this->weeklies->keys();
    }

    public function getByMissionType(MissionType $missionType): Collection
    {
        return match ($missionType) {
            MissionType::ACHIEVEMENT => $this->getAchievements(),
            MissionType::BEGINNER => $this->getBeginners(),
            MissionType::DAILY => $this->getDailies(),
            MissionType::WEEKLY => $this->getWeeklies(),
            default => collect(),
        };
    }

    /**
     * レスポンスするミッションデータのみにフィルタリングする
     * @return UsrMissionNormalBundle
     */
    public function filterForResponse(): self
    {
        $this->achievements = $this->achievements
            ->filter(fn (UsrMissionNormalInterface $usrMission) => $usrMission->isOpen());
        // beginnersは、未開放でもレスポンスする
        $this->dailies = $this->dailies
            ->filter(fn (UsrMissionNormalInterface $usrMission) => $usrMission->isOpen());
        $this->weeklies = $this->weeklies
            ->filter(fn (UsrMissionNormalInterface $usrMission) => $usrMission->isOpen());

        return $this;
    }
}
