<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

readonly class GachaPrizeProbability
{
    public function __construct(
        private GachaBoxInterface $prize,
        private float $probability
    ) {
    }

    /**
     * @return array<string, string|integer|float>
     */
    public function formatToResponse(): array
    {
        return [
            'resourceType' => $this->prize->getResourceType()->value,
            'resourceId' => $this->prize->getResourceId(),
            'resourceAmount' => $this->prize->getResourceAmount(),
            'probability' => $this->probability,
            'isPickup' => $this->prize->getPickup(),
        ];
    }
}
