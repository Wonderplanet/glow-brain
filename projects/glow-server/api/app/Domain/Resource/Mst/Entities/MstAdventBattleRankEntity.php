<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstAdventBattleRankEntity
{
    public function __construct(
        private string $id,
        private string $mstAdventBattleId,
        private string $rankType,
        private int $rankLevel,
        private int $requiredLowerScore,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstAdventBattleId(): string
    {
        return $this->mstAdventBattleId;
    }

    public function getRankType(): string
    {
        return $this->rankType;
    }

    public function getRankLevel(): int
    {
        return $this->rankLevel;
    }

    public function getRequiredLowerScore(): int
    {
        return $this->requiredLowerScore;
    }
}
