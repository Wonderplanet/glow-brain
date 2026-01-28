<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\BoxGacha\Enums\BoxGachaLoopType;

class MstBoxGachaEntity
{
    public function __construct(
        private string $id,
        private string $mstEventId,
        private string $costId,
        private int $costNum,
        private string $loopType,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMstEventId(): string
    {
        return $this->mstEventId;
    }

    public function getCostId(): string
    {
        return $this->costId;
    }

    public function getCostNum(): int
    {
        return $this->costNum;
    }

    public function getLoopType(): string
    {
        return $this->loopType;
    }

    public function getLoopTypeEnum(): BoxGachaLoopType
    {
        return BoxGachaLoopType::from($this->loopType);
    }
}
