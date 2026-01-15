<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Resource\Mst\Entities\Contracts\IMstUnitRankUpEntity;

class MstUnitSpecificRankUpEntity implements IMstUnitRankUpEntity
{
    public function __construct(
        private string $id,
        private string $mstUnitId,
        private int $rank,
        private int $amount,
        private int $unitMemoryAmount,
        private int $requireLevel,
        private int $srMemoryFragmentAmount,
        private int $ssrMemoryFragmentAmount,
        private int $urMemoryFragmentAmount,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstUnitId(): string
    {
        return $this->mstUnitId;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getUnitMemoryAmount(): int
    {
        return $this->unitMemoryAmount;
    }

    public function getRequireLevel(): int
    {
        return $this->requireLevel;
    }

    public function getSrMemoryFragmentAmount(): int
    {
        return $this->srMemoryFragmentAmount;
    }

    public function getSsrMemoryFragmentAmount(): int
    {
        return $this->ssrMemoryFragmentAmount;
    }

    public function getUrMemoryFragmentAmount(): int
    {
        return $this->urMemoryFragmentAmount;
    }

    public function needUnitMemory(): bool
    {
        return $this->unitMemoryAmount > 0;
    }
}
