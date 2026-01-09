<?php

declare(strict_types=1);

namespace App\Domain\Mission\Entities;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Enums\UnreceivedRewardReason;

class MissionReceiveRewardStatus
{
    private MissionType $missionType;
    private string $mstMissionId;
    private ?UnreceivedRewardReason $unreceivedReason = null;

    public function __construct(
        MissionType $missionType,
        string $mstMissionId,
        ?UnreceivedRewardReason $unreceivedReason
    ) {
        $this->missionType = $missionType;
        $this->mstMissionId = $mstMissionId;
        $this->unreceivedReason = $unreceivedReason;
    }

    public function getMissionType(): MissionType
    {
        return $this->missionType;
    }

    public function getMstMissionId(): string
    {
        return $this->mstMissionId;
    }

    public function getUnreceivedReason(): ?UnreceivedRewardReason
    {
        return $this->unreceivedReason;
    }
}
