<?php

declare(strict_types=1);

namespace App\Domain\Mission\Models;

use App\Domain\Mission\Enums\MissionType;
use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $mission_type
 * @property string $received_reward
 */
class LogMissionReward extends LogModel
{
    public function setMissionType(MissionType $missionType): void
    {
        $this->mission_type = $missionType->value;
    }

    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }
}
