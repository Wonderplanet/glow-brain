<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

use Carbon\CarbonImmutable;

class MngInGameNoticeEntity
{
    public function __construct(
        private string $id,
        private string $displayType,
        private bool $enable,
        private int $priority,
        private string $displayFrequencyType,
        private string $destinationType,
        private string $destinationPath,
        private string $destinationPathDetail,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDisplayType(): string
    {
        return $this->displayType;
    }

    public function getEnable(): bool
    {
        return $this->enable;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getDisplayFrequencyType(): string
    {
        return $this->displayFrequencyType;
    }

    public function getDestinationType(): string
    {
        return $this->destinationType;
    }

    public function getDestinationPath(): string
    {
        return $this->destinationPath;
    }

    public function getDestinationPathDetail(): string
    {
        return $this->destinationPathDetail;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function isEnable(): bool
    {
        return $this->enable;
    }

    public function isActive(CarbonImmutable $now): bool
    {
        if (!$this->enable) {
            return false;
        }

        return $now->between(
            $this->startAt,
            $this->endAt,
        );
    }
}
