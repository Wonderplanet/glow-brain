<?php

declare(strict_types=1);

namespace WonderPlanet\Domain\MasterAssetRelease\Entities;

/**
 * MngAssetReleaseVersionのエンティティクラス
 */
readonly class MngAssetReleaseVersionEntity
{
    public function __construct(
        readonly private string $id,
        readonly private int $releaseKey,
        readonly private string $gitRevision,
        readonly private string $gitBranch,
        readonly private string $catalogHash,
        readonly private int $platform,
        readonly private string $buildClientVersion,
        readonly private int $assetTotalByteSize,
        readonly private int $catalogByteSize,
        readonly private string $catalogFileName,
        readonly private string $catalogHashFileName,
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

    public function getGitRevision(): string
    {
        return $this->gitRevision;
    }

    public function getGitBranch(): string
    {
        return $this->gitBranch;
    }

    public function getCatalogHash(): string
    {
        return $this->catalogHash;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function getBuildClientVersion(): string
    {
        return $this->buildClientVersion;
    }

    public function getAssetTotalByteSize(): int
    {
        return $this->assetTotalByteSize;
    }

    public function getCatalogByteSize(): int
    {
        return $this->catalogByteSize;
    }

    public function getCatalogFileName(): string
    {
        return $this->catalogFileName;
    }

    public function getCatalogHashFileName(): string
    {
        return $this->catalogHashFileName;
    }

    public function isRequireUpdate(string $assetHash): bool
    {
        return $assetHash !== $this->getCatalogHash();
    }
}
