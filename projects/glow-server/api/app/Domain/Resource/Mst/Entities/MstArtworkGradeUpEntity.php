<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkGradeUpEntity
{
    public function __construct(
        private string $id,
        private ?string $mstArtworkId,
        private string $mstSeriesId,
        private string $rarity,
        private int $gradeLevel,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkId(): ?string
    {
        return $this->mstArtworkId;
    }

    public function getMstSeriesId(): string
    {
        return $this->mstSeriesId;
    }

    public function getRarity(): string
    {
        return $this->rarity;
    }

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
    }
}
