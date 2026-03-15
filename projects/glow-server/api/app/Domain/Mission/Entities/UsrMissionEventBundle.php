<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Mission\Models\UsrMissionEventInterface;
use Illuminate\Support\Collection;

/**
 * usr_mission_eventsテーブルのユーザーデータをミッションタイプごとに整理してまとめたクラス
 */
class UsrMissionEventBundle
{
    /**
     * @param Collection<string, UsrMissionEventInterface> $events key: mst_mission_event_id
     * @param Collection<string, UsrMissionEventInterface> $eventDailies key: mst_mission_event_daily_id
     */
    public function __construct(
        private Collection $events,
        private Collection $eventDailies,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->events->isEmpty()
            && $this->eventDailies->isEmpty();
    }

    /**
     * @return Collection<string, UsrMissionEventInterface> $events key: mst_mission_event_id
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * @return Collection<string, UsrMissionEventInterface> $eventDailies key: mst_mission_event_daily_id
     */
    public function getEventDailies(): Collection
    {
        return $this->eventDailies;
    }

    public function getByMissionType(MissionType $missionType): Collection
    {
        return match ($missionType) {
            MissionType::EVENT => $this->getEvents(),
            MissionType::EVENT_DAILY => $this->getEventDailies(),
            default => collect(),
        };
    }

    public function getMstMissionEventIds(): Collection
    {
        return $this->events->keys();
    }

    public function getMstMissionEventDailyIds(): Collection
    {
        return $this->eventDailies->keys();
    }

    /**
     * 開放済のユーザーミッションのみでBundleインスタンスを生成して返す
     * @return UsrMissionEventBundle
     */
    public function filterOpened(): self
    {
        $this->events = $this->events
            ->filter(fn (UsrMissionEventInterface $usrMission) => $usrMission->isOpen());
        $this->eventDailies = $this->eventDailies
            ->filter(fn (UsrMissionEventInterface $usrMission) => $usrMission->isOpen());

        return $this;
    }
}
