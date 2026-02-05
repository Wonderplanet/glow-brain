<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use App\Domain\Gacha\Entities\GachaProbabilityGroup;
use App\Domain\Gacha\Entities\GachaRarityProbability;
use Illuminate\Support\Collection;

readonly class StepUpGachaPrizeInfoData
{
    public function __construct(
        private int $stepNumber,
        private int $drawCount,
        private int $fixedPrizeCount,
        private ?string $fixedPrizeRarityThresholdType,
        private Collection $rarityProbabilities,
        private Collection $probabilityGroups
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

    public function getFixedPrizeRarityThresholdType(): ?string
    {
        return $this->fixedPrizeRarityThresholdType;
    }

    public function getRarityProbabilities(): Collection
    {
        return $this->rarityProbabilities;
    }

    public function getProbabilityGroups(): Collection
    {
        return $this->probabilityGroups;
    }

    /**
     * @return array<mixed>
     */
    public function formatToResponse(): array
    {
        return [
            'stepNumber' => $this->stepNumber,
            'drawCount' => $this->drawCount,
            'fixedPrizeCount' => $this->fixedPrizeCount,
            'fixedPrizeRarityThresholdType' => $this->fixedPrizeRarityThresholdType,
            'rarityProbabilities' => $this
                ->rarityProbabilities
                ->map(fn(GachaRarityProbability $prob) => $prob->formatToResponse())
                ->toArray(),
            'probabilityGroups' => $this
                ->probabilityGroups
                ->map(fn(GachaProbabilityGroup $group) => $group->formatToResponse())
                ->toArray(),
        ];
    }
}
