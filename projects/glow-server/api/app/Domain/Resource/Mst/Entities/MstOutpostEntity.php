<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstOutpostEntity
{
    public function __construct(
        private string $id,
        private string $assetKey,
        private string $startAt,
        private string $endAt,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
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
