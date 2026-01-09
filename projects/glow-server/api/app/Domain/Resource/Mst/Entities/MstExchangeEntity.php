<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstExchangeEntity
{
    public function __construct(
        private string $id,
        private string $exchangeTradeType,
        private string $startAt,
        private ?string $endAt,
        private string $lineupGroupId,
        private int $displayOrder,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExchangeTradeType(): string
    {
        return $this->exchangeTradeType;
    }

    public function getStartAt(): string
    {
        return $this->startAt;
    }

    public function getEndAt(): ?string
    {
        return $this->endAt;
    }

    public function getLineupGroupId(): string
    {
        return $this->lineupGroupId;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }
}
