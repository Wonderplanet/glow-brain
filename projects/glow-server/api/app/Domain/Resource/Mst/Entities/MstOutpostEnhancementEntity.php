<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstOutpostEnhancementEntity
{
    public function __construct(
        private string $id,
        private string $mstOutpostId,
        private string $outpostEnhancementType,
        private string $assetKey,
        private int $releaseKey,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstOutpostId(): string
    {
        return $this->mstOutpostId;
    }

    public function getOutpostEnhancementType(): string
    {
        return $this->outpostEnhancementType;
    }

    public function getAssetKey(): string
    {
        return $this->assetKey;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }
}
