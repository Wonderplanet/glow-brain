<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

readonly class MstCheatSettingEntity
{
    public function __construct(
        private string $id,
        private string $contentType,
        private string $cheatType,
        private int $cheatValue,
        private int $isExcludedRanking,
        private string $startAt,
        private string $endAt,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getCheatType(): string
    {
        return $this->cheatType;
    }

    public function getCheatValue(): int
    {
        return $this->cheatValue;
    }

    public function isExcludedRanking(): bool
    {
        return (bool) $this->isExcludedRanking;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
