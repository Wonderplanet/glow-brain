<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPartyUnitCountEntity
{
    public function __construct(
        private string $id,
        private string $mstStageId,
        private int $maxCount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    public function getMaxCount(): int
    {
        return $this->maxCount;
    }
}
