<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstMissionEventDailyBonusScheduleEntity
{
    public function __construct(
        private string $id,
        private string $mstEventId,
        private string $startAt,
        private string $endAt,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getMstEventId(): string
    {
        return $this->mstEventId;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }
}
