<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

class MstExchangeLineupEntity
{
    public function __construct(
        private string $id,
        private string $groupId,
        private ?int $tradableCount,
        private int $displayOrder,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getGroupId(): string
    {
        return $this->groupId;
    }

    public function getTradableCount(): ?int
    {
        return $this->tradableCount;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }
}
