<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstUnitRankCoefficientEntity
{
    public function __construct(
        private string $id,
        private int $rank,
        private int $coefficient,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getCoefficient(): int
    {
        return $this->coefficient;
    }
}
