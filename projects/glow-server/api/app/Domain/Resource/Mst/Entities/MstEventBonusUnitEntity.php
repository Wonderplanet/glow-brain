<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstEventBonusUnitEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $mstUnitId,
        private int $bonusPercentage,
        private string $eventBonusGroupId,
        private int $isPickUp,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getBonusPercentage(): int
    {
        return $this->bonusPercentage;
    }

    public function getEventBonusGroupId(): string
    {
        return $this->eventBonusGroupId;
    }

    public function getIsPickUp(): int
    {
        return $this->isPickUp;
    }
}
