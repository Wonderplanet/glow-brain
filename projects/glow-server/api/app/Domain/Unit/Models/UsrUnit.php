<?php

declare(strict_types=1);

namespace App\Domain\Unit\Models;

use App\Domain\Resource\Enums\EncyclopediaCollectStatus;
use App\Domain\Resource\Usr\Entities\UsrUnitEntity;
use App\Domain\Resource\Usr\Models\UsrModel;
use App\Domain\Unit\Constants\UnitConstant;

class UsrUnit extends UsrModel implements UsrUnitInterface
{
    protected static string $tableName = 'usr_units';
    protected array $modelKeyColumns = ['usr_user_id', 'mst_unit_id'];

    public function init(): void
    {
        $this->attributes['level'] = UnitConstant::FIRST_UNIT_LEVEL;
        $this->attributes['rank'] = UnitConstant::FIRST_UNIT_RANK;
        $this->attributes['grade_level'] = UnitConstant::FIRST_UNIT_GRADE_LEVEL;
        $this->attributes['battle_count'] = 0;
        $this->attributes['is_new_encyclopedia'] = EncyclopediaCollectStatus::IS_NEW->value;
        $this->attributes['last_reward_grade_level'] = 1;
    }

    public static function create(string $usrUserId, string $mstUnitId): UsrUnitInterface
    {
        return new self([
            'usr_user_id' => $usrUserId,
            'mst_unit_id' => $mstUnitId,
            'level' => 1,
            'rank' => 0,
            'grade_level' => 1,
            'battle_count' => 0,
            'is_new_encyclopedia' => EncyclopediaCollectStatus::IS_NEW->value,
            'last_reward_grade_level' => 1,
        ]);
    }

    public function getMstUnitId(): string
    {
        return $this->attributes['mst_unit_id'];
    }

    public function getLevel(): int
    {
        return $this->attributes['level'];
    }

    public function setLevel(int $level): void
    {
        $this->attributes['level'] = $level;
    }

    public function getRank(): int
    {
        return $this->attributes['rank'];
    }

    public function setRank(int $rank): void
    {
        $this->attributes['rank'] = $rank;
    }

    public function getGradeLevel(): int
    {
        return $this->attributes['grade_level'];
    }

    public function incrementGradeLevel(): void
    {
        $this->attributes['grade_level']++;
    }

    public function getBattleCount(): int
    {
        return $this->attributes['battle_count'];
    }

    public function incrementBattleCount(): void
    {
        $this->attributes['battle_count']++;
    }

    public function addBattleCount(int $addNum): void
    {
        $this->attributes['battle_count'] += $addNum;
    }

    public function getIsNewEncyclopedia(): int
    {
        return $this->attributes['is_new_encyclopedia'];
    }

    public function markAsCollected(): void
    {
        $this->attributes['is_new_encyclopedia'] = EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function isAlreadyCollected(): bool
    {
        return $this->attributes['is_new_encyclopedia'] === EncyclopediaCollectStatus::IS_NOT_NEW->value;
    }

    public function getLastRewardGradeLevel(): int
    {
        return $this->attributes['last_reward_grade_level'];
    }

    public function setLastRewardGradeLevel(int $gradeLevel): void
    {
        $this->attributes['last_reward_grade_level'] = $gradeLevel;
    }

    public function toEntity(): UsrUnitEntity
    {
        return new UsrUnitEntity(
            $this->getId(),
            $this->getUsrUserId(),
            $this->getMstUnitId(),
            $this->getLevel(),
            $this->getRank(),
            $this->getGradeLevel(),
            $this->getBattleCount(),
            $this->getIsNewEncyclopedia(),
            $this->getLastRewardGradeLevel(),
        );
    }
}
