<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstPvpBonusPointEntity
{
    public function __construct(
        private string $id,
        private string $conditionValue,
        private int $bonusPoint,
        private string $bonusType,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getConditionValue(): string
    {
        return $this->conditionValue;
    }

    public function getConditionValueAsInt(): int
    {
        return (int)$this->conditionValue;
    }

    public function getConditionValueAsFloat(): float
    {
        return (float)$this->conditionValue;
    }

    public function getBonusPoint(): int
    {
        return $this->bonusPoint;
    }

    public function getBonusType(): string
    {
        return $this->bonusType;
    }
}
