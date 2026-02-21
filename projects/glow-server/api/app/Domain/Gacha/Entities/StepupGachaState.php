<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Entities;

use App\Domain\Resource\Mst\Entities\OprStepupGachaEntity;
use App\Domain\Resource\Mst\Entities\OprStepupGachaStepEntity;

readonly class StepupGachaState
{
    public function __construct(
        private OprStepupGachaEntity $stepupGacha,
        private OprStepupGachaStepEntity $stepupGachaStep,
        private int $currentStepNumber,
        private int $loopCount
    ) {
    }

    public function getStepupGacha(): OprStepupGachaEntity
    {
        return $this->stepupGacha;
    }

    public function getStepupGachaStep(): OprStepupGachaStepEntity
    {
        return $this->stepupGachaStep;
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
