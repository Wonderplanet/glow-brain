<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstAdventBattleRewardGroupEntity
{
    public function __construct(
        private string $id,
        private string $mstAdventBattleId,
        private string $rewardCategory,
        private string $conditionValue,
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

    public function getRewardCategory(): string
    {
        return $this->rewardCategory;
    }

    public function getConditionValue(): string
    {
        return $this->conditionValue;
    }
}
