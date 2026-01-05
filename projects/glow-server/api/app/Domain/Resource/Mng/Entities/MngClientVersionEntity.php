<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mng\Entities;

class MngClientVersionEntity
{
    public function __construct(
        private string $id,
        private string $clientVersion,
        private int $platform,
        private bool $isForceUpdate,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientVersion(): string
    {
        return $this->clientVersion;
    }

    public function getPlatform(): int
    {
        return $this->platform;
    }

    public function getIsForceUpdate(): bool
    {
        return $this->isForceUpdate;
    }

    public function isRequireUpdate(): bool
    {
        return $this->isForceUpdate;
    }
}
