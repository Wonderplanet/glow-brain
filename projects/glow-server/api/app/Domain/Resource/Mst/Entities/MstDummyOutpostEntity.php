<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstDummyOutpostEntity
{
    public function __construct(
        private string $id,
        private string $mstDummyUserId,
        private string $mstOutpostEnhancementId,
        private int $level
    ) {
    }
    public function getId(): string
    {
        return $this->id;
    }
    public function getMstDummyUserId(): string
    {
        return $this->mstDummyUserId;
    }
    public function getMstOutpostEnhancementId(): string
    {
        return $this->mstOutpostEnhancementId;
    }
    public function getLevel(): int
    {
        return $this->level;
    }
}
