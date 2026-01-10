<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

/**
 * OprAssetReleaseのエンティティクラス
 */
class OprAssetReleaseEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private int $platform,
        readonly private bool $enabled,
        readonly private string $targetReleaseVersionId,
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

    public function getTargetReleaseVersionId(): string
    {
        return $this->targetReleaseVersionId;
    }
}
