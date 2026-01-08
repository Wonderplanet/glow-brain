<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrOutpostEnhancementEntity
{
    public function __construct(
        private string $mstOutpostId,
        private string $mstOutpostEnhancementId,
        private int $level,
    ) {
    }

    public function getMstOutpostId(): string
    {
        return $this->mstOutpostId;
    }

    public function getMstOutpostEnhancementId(): string
    {
        return $this->mstOutpostEnhancementId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mst_outpost_id' => $this->mstOutpostId,
            'mst_outpost_enhancement_id' => $this->mstOutpostEnhancementId,
            'level' => $this->level,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'mstOutpostId' => $this->mstOutpostId,
            'mstOutpostEnhancementId' => $this->mstOutpostEnhancementId,
            'level' => $this->level,
        ];
    }
}
