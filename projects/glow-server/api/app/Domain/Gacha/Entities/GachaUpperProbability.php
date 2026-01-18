<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use Illuminate\Support\Collection;

readonly class GachaUpperProbability
{
    public function __construct(
        private string $upperType,
        private Collection $probabilityGroups,
        private Collection $rarityProbabilities,
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'upperType' => $this->upperType,
            'rarityProbabilities' => $this
                ->rarityProbabilities
                ->map(fn(GachaRarityProbability $rarityProbability) => $rarityProbability->formatToResponse())
                ->toArray(),
            'probabilityGroups' => $this
                ->probabilityGroups
                ->map(fn(GachaProbabilityGroup $group) => $group->formatToResponse())
                ->toArray(),
        ];
    }
}
