<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Gacha\Enums\CostType;

class OprGachaUseResourceEntity
{
    public function __construct(
        private string $id,
        private string $oprGachaId,
        private CostType $costType,
        private ?string $costId,
        private int $costNum,
        private int $drawCount,
        private int $costPriority,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getCostType(): CostType
    {
        return $this->costType;
    }

    public function getCostId(): ?string
    {
        return $this->costId;
    }

    public function getCostNum(): int
    {
        return $this->costNum;
    }

    public function getDrawCount(): int
    {
        return $this->drawCount;
    }

    public function getCostPriority(): int
    {
        return $this->costPriority;
    }
}
