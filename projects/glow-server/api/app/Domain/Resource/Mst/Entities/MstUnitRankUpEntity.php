<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Resource\Mst\Entities\Contracts\IMstUnitRankUpEntity;

class MstUnitRankUpEntity implements IMstUnitRankUpEntity
{
    public function __construct(
        private string $id,
        private string $unitLabel,
        private int $rank,
        private int $amount,
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

    public function getUnitLabel(): string
    {
        return $this->unitLabel;
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
        return 0;
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

    public function getMstUnitId(): string
    {
        return '';
    }

    public function needUnitMemory(): bool
    {
        return false;
    }
}
