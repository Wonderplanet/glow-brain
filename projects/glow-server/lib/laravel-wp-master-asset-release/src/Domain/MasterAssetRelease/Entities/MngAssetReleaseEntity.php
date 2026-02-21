<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Entities;

use Carbon\CarbonImmutable;

/**
 * OprAssetReleaseのエンティティクラス
 */
readonly class MngAssetReleaseEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private int $platform,
        readonly private bool $enabled,
        readonly private string|null $targetReleaseVersionId,
        readonly private string|null $clientCompatibilityVersion,
        readonly private string|null $description,
        readonly private ?CarbonImmutable $startAt,
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

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function getTargetReleaseVersionId(): string|null
    {
        return $this->targetReleaseVersionId;
    }

    public function getClientCompatibilityVersion(): string|null
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
}
