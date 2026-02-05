<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstArtworkEffectEntity
{
    public function __construct(
        private string $id,
        private string $mstArtworkId,
        private string $effectType,
        private float $gradeLevel1Value,
        private float $gradeLevel2Value,
        private float $gradeLevel3Value,
        private float $gradeLevel4Value,
        private float $gradeLevel5Value,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstArtworkId(): string
    {
        return $this->mstArtworkId;
    }

    public function getEffectType(): string
    {
        return $this->effectType;
    }

    public function getGradeLevel1Value(): float
    {
        return $this->gradeLevel1Value;
    }

    public function getGradeLevel2Value(): float
    {
        return $this->gradeLevel2Value;
    }

    public function getGradeLevel3Value(): float
    {
        return $this->gradeLevel3Value;
    }

    public function getGradeLevel4Value(): float
    {
        return $this->gradeLevel4Value;
    }

    public function getGradeLevel5Value(): float
    {
        return $this->gradeLevel5Value;
    }
}
