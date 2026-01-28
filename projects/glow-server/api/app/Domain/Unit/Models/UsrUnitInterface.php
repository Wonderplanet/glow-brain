<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Resource\Usr\Models\Contracts\UsrModelInterface;

interface UsrUnitInterface extends UsrModelInterface
{
    public function getMstUnitId(): string;

    public function getLevel(): int;

    public function setLevel(int $level): void;

    public function getRank(): int;

    public function setRank(int $rank): void;

    public function getGradeLevel(): int;

    public function incrementGradeLevel(): void;

    public function getBattleCount(): int;

    public function incrementBattleCount(): void;

    public function addBattleCount(int $addNum): void;

    public function getIsNewEncyclopedia(): int;

    public function markAsCollected(): void;

    public function isAlreadyCollected(): bool;

    public function getLastRewardGradeLevel(): int;

    public function setLastRewardGradeLevel(int $gradeLevel): void;

    public function toEntity(): UsrUnitEntity;
}
