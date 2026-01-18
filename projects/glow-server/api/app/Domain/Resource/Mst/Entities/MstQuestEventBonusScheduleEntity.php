<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstQuestEventBonusScheduleEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $mstQuestId,
        private string $eventBonusGroupId,
        private string $startAt,
        private string $endAt,
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

    public function getMstQuestId(): string
    {
        return $this->mstQuestId;
    }

    public function getEventBonusGroupId(): string
    {
        return $this->eventBonusGroupId;
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
