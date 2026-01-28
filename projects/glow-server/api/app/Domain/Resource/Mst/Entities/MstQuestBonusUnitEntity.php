<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstQuestBonusUnitEntity
{
    public function __construct(
        private string $id,
        private string $mstQuestId,
        private string $mstUnitId,
        private float $coinBonusRate,
        private string $startAt,
        private string $endAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstQuestId(): string
    {
        return $this->mstQuestId;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getCoinBonusRate(): float
    {
        return $this->coinBonusRate;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): string
    {
        return $this->endAt;
    }
}
