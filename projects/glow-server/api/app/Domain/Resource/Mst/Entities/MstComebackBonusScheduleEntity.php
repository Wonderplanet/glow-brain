<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstComebackBonusScheduleEntity
{
    public function __construct(
        private string $id,
        private int $inactiveConditionDays,
        private int $durationDays,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getInactiveConditionDays(): int
    {
        return $this->inactiveConditionDays;
    }

    public function getDurationDays(): int
    {
        return $this->durationDays;
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
