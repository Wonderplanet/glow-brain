<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUserLevelEntity
{
    public function __construct(
        private string $id,
        private int $level,
        private int $stamina,
        private int $exp,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function getStamina(): int
    {
        return $this->stamina;
    }

    public function getExp(): int
    {
        return $this->exp;
    }
}
