<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Gacha\Entities\GachaProbabilityGroup;
use App\Domain\Gacha\Entities\GachaRarityProbability;
use Illuminate\Support\Collection;

readonly class StepupGachaPrizeInfoData
{
    public function __construct(
        private int $stepNumber,
        private int $drawCount,
        private int $fixedPrizeCount,
        private Collection $rarityProbabilities,
        private Collection $probabilityGroups,
        private Collection $fixedRarityProbabilities,
        private Collection $fixedProbabilityGroups,
    ) {
    }

    public function getStepNumber(): int
    {
        return $this->stepNumber;
    }

    public function getDrawCount(): int
    {
        return $this->drawCount;
    }

    public function getFixedPrizeCount(): int
    {
        return $this->fixedPrizeCount;
    }

    public function getRarityProbabilities(): Collection
    {
        return $this->rarityProbabilities;
    }

    public function getProbabilityGroups(): Collection
    {
        return $this->probabilityGroups;
    }

    public function getFixedRarityProbabilities(): Collection
    {
        return $this->fixedRarityProbabilities;
    }

    public function getFixedProbabilityGroups(): Collection
    {
        return $this->fixedProbabilityGroups;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'stepNumber' => $this->stepNumber,
            'drawCount' => $this->drawCount,
            'rarityProbabilities' => $this
                ->rarityProbabilities
                ->map(fn(GachaRarityProbability $prob) => $prob->formatToResponse())
                ->toArray(),
            'probabilityGroups' => $this
                ->probabilityGroups
                ->map(fn(GachaProbabilityGroup $group) => $group->formatToResponse())
                ->toArray(),
            'fixedProbabilities' => [
                'fixedCount' => $this->fixedPrizeCount,
                'rarityProbabilities' => $this
                    ->fixedRarityProbabilities
                    ->map(fn(GachaRarityProbability $prob) => $prob->formatToResponse())
                    ->toArray(),
                'probabilityGroups' => $this
                    ->fixedProbabilityGroups
                    ->map(fn(GachaProbabilityGroup $group) => $group->formatToResponse())
                    ->toArray(),
            ],
        ];
    }
}
