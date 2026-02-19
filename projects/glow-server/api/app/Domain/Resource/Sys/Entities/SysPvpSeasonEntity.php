<?php

declare(strict_types=1);

namespace App\Domain\Resource\Sys\Entities;

use Carbon\CarbonImmutable;

class SysPvpSeasonEntity
{
    public function __construct(
        private string $id,
        private CarbonImmutable $startAt,
        private CarbonImmutable $endAt,
        private ?CarbonImmutable $closedAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStartAt(): CarbonImmutable
    {
        return $this->startAt;
    }

    public function getEndAt(): CarbonImmutable
    {
        return $this->endAt;
    }

    public function getClosedAt(): ?CarbonImmutable
    {
        return $this->closedAt;
    }

    public function isInSeason(CarbonImmutable $time): bool
    {
        return $time->between($this->startAt, $this->endAt);
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->getId(),
            'startAt' => $this->getStartAt()->toIso8601String(),
            'endAt' => $this->getEndAt()->toIso8601String(),
            'closedAt' => $this->getClosedAt()?->toIso8601String(),
        ];
    }
}
