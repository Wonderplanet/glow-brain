<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Entities;

use Carbon\CarbonImmutable;

/**
 * MngMasterReleaseのエンティティクラス
 */
readonly class MngMasterReleaseEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private int $enabled,
        readonly private string|null $targetReleaseVersionId,
        readonly private string $clientCompatibilityVersion,
        readonly private string|null $description,
        readonly private ?CarbonImmutable $startAt,
        readonly private ?CarbonImmutable $createdAt,
        readonly private ?CarbonImmutable $updatedAt
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

    public function isEnabled(): bool
    {
        return (bool)$this->enabled;
    }

    public function getTargetReleaseVersionId(): string|null
    {
        return $this->targetReleaseVersionId;
    }

    public function getClientCompatibilityVersion(): string
    {
        return $this->clientCompatibilityVersion;
    }

    public function getDescription(): string|null
    {
        return $this->description;
    }

    public function getStartAt(): ?CarbonImmutable
    {
        return $this->startAt;
    }

    public function getCreatedAt(): ?CarbonImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?CarbonImmutable
    {
        return $this->updatedAt;
    }
}
