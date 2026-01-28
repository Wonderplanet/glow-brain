<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstBoxGachaGroupEntity
{
    public function __construct(
        private string $id,
        private string $mstBoxGachaId,
        private int $boxLevel,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstBoxGachaId(): string
    {
        return $this->mstBoxGachaId;
    }

    public function getBoxLevel(): int
    {
        return $this->boxLevel;
    }
}
