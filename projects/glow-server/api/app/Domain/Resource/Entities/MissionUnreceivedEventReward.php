<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

use Illuminate\Support\Collection;

/**
 * イベントミッションの報酬未受け取り数情報をイベントカテゴリ毎に格納するデータクラス
 */
class MissionUnreceivedEventReward
{
    /**
     * @param Collection<string, int> $eventCountMap key: mst_event_id, value: count
     * @param Collection<string, int> $eventDailyCountMap key: mst_event_id, value: count
     */
    public function __construct(
        private Collection $eventCountMap,
        private Collection $eventDailyCountMap,
    ) {
        $this->eventCountMap = $eventCountMap;
        $this->eventDailyCountMap = $eventDailyCountMap;
    }

    /**
     * イベントミッション、イベントデイリーミッションの報酬未受け取り数をイベントIDごとに合算して返す
     *
     * @return Collection<string, int> key: mst_event_id, value: count
     */
    public function getCountForEventId(): Collection
    {
        return $this->eventCountMap->mergeRecursive($this->eventDailyCountMap)
            ->map(fn ($counts) => array_sum((array) $counts));
    }

    public function getEventCount(): Collection
    {
        return $this->eventCountMap;
    }

    public function getEventDailyCount(): Collection
    {
        return $this->eventDailyCountMap;
    }
}
