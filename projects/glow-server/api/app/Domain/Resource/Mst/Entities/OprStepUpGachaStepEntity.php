<?php

declare(strict_types=1);

namespace App\Domain\Resource\Mst\Entities;

use App\Domain\Gacha\Enums\CostType;
use App\Domain\Resource\Enums\RarityType;

class OprStepUpGachaStepEntity
{
    public function __construct(
        private string $id,
        private int $releaseKey,
        private string $oprGachaId,
        private int $stepNumber,
        private CostType $costType,
        private ?string $costId,
        private int $costNum,
        private int $drawCount,
        private int $fixedPrizeCount,
        private ?RarityType $fixedPrizeRarityThresholdType,
        private ?string $prizeGroupId,
        private ?string $fixedPrizeGroupId,
        private bool $isFirstFree,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getReleaseKey(): int
    {
        return $this->releaseKey;
    }

    public function getOprGachaId(): string
    {
        return $this->oprGachaId;
    }

    public function getStepNumber(): int
    {
        return $this->stepNumber;
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

    public function getFixedPrizeCount(): int
    {
        return $this->fixedPrizeCount;
    }

    public function getFixedPrizeRarityThresholdType(): ?RarityType
    {
        return $this->fixedPrizeRarityThresholdType;
    }

    public function getPrizeGroupId(): ?string
    {
        return $this->prizeGroupId;
    }

    public function getFixedPrizeGroupId(): ?string
    {
        return $this->fixedPrizeGroupId;
    }

    public function getIsFirstFree(): bool
    {
        return $this->isFirstFree;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'releaseKey' => $this->releaseKey,
            'oprGachaId' => $this->oprGachaId,
            'stepNumber' => $this->stepNumber,
            'costType' => $this->costType->value,
            'costId' => $this->costId,
            'costNum' => $this->costNum,
            'drawCount' => $this->drawCount,
            'fixedPrizeCount' => $this->fixedPrizeCount,
            'fixedPrizeRarityThresholdType' => $this->fixedPrizeRarityThresholdType?->value,
            'prizeGroupId' => $this->prizeGroupId,
            'fixedPrizeGroupId' => $this->fixedPrizeGroupId,
            'isFirstFree' => $this->isFirstFree,
        ];
    }
}
