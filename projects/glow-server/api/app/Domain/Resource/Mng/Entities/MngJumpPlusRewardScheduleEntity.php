<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;

class MngJumpPlusRewardScheduleEntity
{
    public function __construct(
        private string $id,
        private string $groupId,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function isActive(CarbonImmutable $now): bool
    {
        return $now->between(
            $this->startAt,
            $this->endAt,
        );
    }
}
