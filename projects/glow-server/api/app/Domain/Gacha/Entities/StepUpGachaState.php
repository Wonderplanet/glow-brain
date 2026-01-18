<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Resource\Mst\Entities\OprStepUpGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepUpGachaStepEntity;

readonly class StepUpGachaState
{
    public function __construct(
        private OprStepUpGachaEntity $stepUpGacha,
        private OprStepUpGachaStepEntity $stepUpGachaStep,
        private int $currentStepNumber,
        private int $loopCount
    ) {
    }

    public function getStepUpGacha(): OprStepUpGachaEntity
    {
        return $this->stepUpGacha;
    }

    public function getStepUpGachaStep(): OprStepUpGachaStepEntity
    {
        return $this->stepUpGachaStep;
    }

    public function getCurrentStepNumber(): int
    {
        return $this->currentStepNumber;
    }

    public function getLoopCount(): int
    {
        return $this->loopCount;
    }
}
