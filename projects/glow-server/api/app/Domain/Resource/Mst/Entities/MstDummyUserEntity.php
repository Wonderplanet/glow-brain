<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstDummyUserEntity
{
    public function __construct(
        private string $id,
        private string $mstUnitId,
        private string $mstEmblemId,
        private int $gradeUnitLevelTotalCount,
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

    public function getMstEmblemId(): string
    {
        return $this->mstEmblemId;
    }

    public function getGradeUnitLevelTotalCount(): int
    {
        return $this->gradeUnitLevelTotalCount;
    }
    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'id' => $this->id,
            'mstUnitId' => $this->mstUnitId,
            'mstDummyUserId' => $this->mstEmblemId,
            'level' => $this->gradeUnitLevelTotalCount,
        ];
    }
}
