<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitGradeUpEntity
{
    public function __construct(
        private string $id,
        private string $unit_label,
        private int $grade_level,
        private int $require_amount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGradeLevel(): int
    {
        return $this->grade_level;
    }

    public function getUnitLabel(): string
    {
        return $this->unit_label;
    }

    public function getRequireAmount(): int
    {
        return $this->require_amount;
    }
}
