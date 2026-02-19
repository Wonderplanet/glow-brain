<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\AdventBattle\Enums\AdventBattleClearRewardCategory;

class MstAdventBattleClearRewardEntity
{
    public function __construct(
        private string $id,
        private string $mstAdventBattleId,
        private string $rewardCategory,
        private string $resourceType,
        private ?string $resourceId,
        private ?int $resourceAmount,
        private int $percentage,
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

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getResourceId(): ?string
    {
        return $this->resourceId;
    }

    public function getResourceAmount(): ?int
    {
        return $this->resourceAmount;
    }

    public function getPercentage(): int
    {
        return $this->percentage;
    }

    public function isFirstClear(): bool
    {
        return $this->rewardCategory === AdventBattleClearRewardCategory::FIRST_CLEAR->value;
    }

    public function isAlways(): bool
    {
        return $this->rewardCategory === AdventBattleClearRewardCategory::ALWAYS->value;
    }

    public function isRandom(): bool
    {
        return $this->rewardCategory === AdventBattleClearRewardCategory::RANDOM->value;
    }
}
