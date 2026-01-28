<?php

declare(strict_types=1);

namespace App\Domain\Common\Entities;

class MasterReleaseVersionEntity
{
    public function __construct(
        public int $releaseKey = 1,
        public string $mstHash = 'mstHash',
        public string $oprHash = 'oprHash',
        public string $dbName = 'dbName',
    ) {
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getMstHash(): string
    {
        return $this->mstHash;
    }

    public function getOprHash(): string
    {
        return $this->oprHash;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }
}
