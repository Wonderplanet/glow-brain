<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitGradeCoefficientEntity
{
    public function __construct(
        private string $id,
        private string $unitLabel,
        private int $gradeLevel,
        private int $coefficient,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUnitLabel(): string
    {
        return $this->unitLabel;
    }

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
    }

    public function getCoefficient(): int
    {
        return $this->coefficient;
    }

    public function makeUnitLabelAndGradeLevelKey(): string
    {
        return self::makeUnitLabelAndGradeLevelKeyStatic($this->unitLabel, $this->gradeLevel);
    }

    public static function makeUnitLabelAndGradeLevelKeyStatic(string $unitLabel, int $gradeLevel): string
    {
        return $unitLabel . '_' . $gradeLevel;
    }
}
