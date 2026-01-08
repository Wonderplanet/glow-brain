<?php

declare(strict_types=1);

namespace App\Domain\Encyclopedia\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $received_reward
 */
class LogEncyclopediaReward extends LogModel
{
    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }
}
