<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitLevelUpEntity
{
    public function __construct(
        private string $id,
        private string $unit_label,
        private int $level,
        private int $required_coin,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUnitLabel(): string
    {
        return $this->unit_label;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getRequiredCoin(): int
    {
        return $this->required_coin;
    }
}
