<?php

declare(strict_types=1);

namespace App\Http\Responses\Data;

use Illuminate\Support\Collection;

class UsrMissionBonusPointData
{
    /**
     * @param string $missionType
     * @param integer $point
     * @param Collection<int> $receivedRewardPoints
     */
    public function __construct(
        private string $missionType,
        private int $point,
        private Collection $receivedRewardPoints,
    ) {
    }

    public function getMissionType(): string
    {
        return $this->missionType;
    }

    public function getPoint(): int
    {
        return $this->point;
    }

    public function getReceivedRewardPoints(): Collection
    {
        return $this->receivedRewardPoints;
    }
}
