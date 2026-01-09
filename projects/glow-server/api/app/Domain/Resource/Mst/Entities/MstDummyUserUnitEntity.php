<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstDummyUserUnitEntity
{
    public function __construct(
        private string $id,
        private string $mstUnitId,
        private string $mstDummyUserId,
        private int $level,
        private int $rank,
        private int $gradeLevel
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getMstDummyUserId(): string
    {
        return $this->mstDummyUserId;
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
            'id' => $this->id,
            'mstUnitId' => $this->mstUnitId,
            'mstDummyUserId' => $this->mstDummyUserId,
            'level' => $this->level,
            'rank' => $this->rank,
            'gradeLevel' => $this->gradeLevel,
        ];
    }
}
