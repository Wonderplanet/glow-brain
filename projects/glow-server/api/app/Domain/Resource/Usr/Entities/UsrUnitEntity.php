<?php

declare(strict_types=1);

namespace App\Domain\Resource\Usr\Entities;

class UsrUnitEntity
{
    public function __construct(
        private string $usrUnitId,
        private string $usrUserId,
        private string $mstUnitId,
        private int $level,
        private int $rank,
        private int $gradeLevel,
        private int $battleCount,
        private int $isNewEncyclopedia,
        private int $lastRewardGradeLevel,
    ) {
    }

    public function getId(): string
    {
        return $this->usrUnitId;
    }

    public function getUsrUnitId(): string
    {
        return $this->usrUnitId;
    }

    public function getUsrUserId(): string
    {
        return $this->usrUserId;
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

    public function getBattleCount(): int
    {
        return $this->battleCount;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->isNewEncyclopedia;
    }

    public function getLastRewardGradeLevel(): int
    {
        return $this->lastRewardGradeLevel;
    }

    /**
     * @return array<mixed>
     */
    public function formatToLog(): array
    {
        return [
            'mst_unit_id' => $this->mstUnitId,
            'level' => $this->level,
            'rank' => $this->rank,
            'grade_level' => $this->gradeLevel,
        ];
    }
}
