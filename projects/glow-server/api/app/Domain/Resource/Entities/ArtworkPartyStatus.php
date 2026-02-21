<?php

declare(strict_types=1);

namespace App\Domain\Resource\Entities;

class ArtworkPartyStatus
{
    public function __construct(
        private readonly string $mstArtworkId,
        private readonly int $gradeLevel,
    ) {
    }

    public function getMstArtworkId(): string
    {
        return $this->mstArtworkId;
    }

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mstArtworkId' => $this->mstArtworkId,
            'gradeLevel' => $this->gradeLevel,
        ];
    }
}
