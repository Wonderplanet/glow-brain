<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Illuminate\Support\Collection;

readonly class GachaProbabilityGroup
{
    public function __construct(
        private string $rarity,
        private Collection $prizeProbabilities
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rarity' => $this->rarity,
            'prizes' => $this
                ->prizeProbabilities
                ->map(fn(GachaPrizeProbability $prize) => $prize->formatToResponse())
                ->toArray(),
        ];
    }
}
