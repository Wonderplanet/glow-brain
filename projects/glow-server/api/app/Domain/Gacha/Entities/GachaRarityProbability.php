<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

readonly class GachaRarityProbability
{
    public function __construct(
        private string $rarity,
        private float $probability
    ) {
    }

    /**
     * @return array<string, string|float>
     */
    public function formatToResponse(): array
    {
        return [
            'rarity' => $this->rarity,
            'probability' => $this->probability,
        ];
    }
}
