<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

/**
 * OprMasterReleaseのエンティティクラス
 */
class OprMasterReleaseEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private int $enabled,
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

    public function isEnabled(): bool
    {
        return (bool) $this->enabled;
    }

    public function getTargetReleaseVersionId(): string
    {
        return $this->targetReleaseVersionId;
    }
}
