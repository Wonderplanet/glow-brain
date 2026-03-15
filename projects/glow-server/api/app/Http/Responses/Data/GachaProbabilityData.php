<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

readonly class GachaProbabilityData
{
    public function __construct(
        private Collection $rarityProbabilities,
        private Collection $probabilityGroups,
        private int $fixedPrizeCount,
        private Collection $fixedProbabilityGroups,
        private Collection $fixedRarityProbabilities,
        private Collection $upperProbabilities
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'rarityProbabilities' => $this
                ->rarityProbabilities
                ->map(fn($rarityProbability) => $rarityProbability->formatToResponse())
                ->toArray(),
            'probabilityGroups' => $this
                ->probabilityGroups
                ->map(fn($probabilityGroup) => $probabilityGroup->formatToResponse())
                ->toArray(),
            'fixedProbabilities' => [
                'fixedCount' => $this->fixedPrizeCount,
                'rarityProbabilities' => $this
                    ->fixedRarityProbabilities
                    ->map(fn($rarityProbability) => $rarityProbability->formatToResponse())
                    ->toArray(),
                'probabilityGroups' => $this
                    ->fixedProbabilityGroups
                    ->map(fn($probabilityGroup) => $probabilityGroup->formatToResponse())
                    ->toArray(),
            ],
            'upperProbabilities' => $this
                ->upperProbabilities
                ->map(fn($upperProbability) => $upperProbability->formatToResponse())
                ->toArray(),
        ];
    }
}
