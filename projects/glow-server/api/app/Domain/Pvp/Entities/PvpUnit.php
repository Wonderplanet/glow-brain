<?php

declare(strict_types=1);

namespace App\Domain\Pvp\Entities;

class PvpUnit
{
    public function __construct(
        private string $mstUnitId,
        private int $level,
        private int $rank,
        private int $gradeLevel,
    ) {
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getGradeLevel(): int
    {
        return $this->gradeLevel;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'mstUnitId' => $this->mstUnitId,
            'level' => $this->level,
            'rank' => $this->rank,
            'gradeLevel' => $this->gradeLevel,
        ];
    }
}
