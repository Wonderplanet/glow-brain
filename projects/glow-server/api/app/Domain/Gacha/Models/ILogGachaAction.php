<?php

declare(strict_types=1);

namespace App\Domain\Gacha\Models;

use App\Domain\Resource\Log\Models\Contracts\LogModelInterface;

interface ILogGachaAction extends LogModelInterface
{
    public function setOprGachaId(string $oprGachaId): void;

    public function setCostType(string $costType): void;

    public function setDrawCount(int $drawCount): void;

    public function setMaxRarityUpperCount(int $maxRarityUpperCount): void;

    public function setPickupUpperCount(int $pickupUpperCount): void;

    public function setStepNumber(?int $stepNumber): void;

    public function setLoopCount(?int $loopCount): void;
}
