<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

class UsrStageEnhanceStatusData
{
    public function __construct(
        private string $mstStageId,
        private int $resetChallengeCount,
        private int $resetAdChallengeCount,
        private int $maxScore,
    ) {
    }

    public function getMstStageId(): string
    {
        return $this->mstStageId;
    }

    public function getResetChallengeCount(): int
    {
        return $this->resetChallengeCount;
    }

    public function getResetAdChallengeCount(): int
    {
        return $this->resetAdChallengeCount;
    }

    public function getMaxScore(): int
    {
        return $this->maxScore;
    }
}
