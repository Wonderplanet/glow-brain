<?php

declare(strict_types=1);

namespace App\Domain\Message\Models;

use App\Domain\Resource\Log\Models\LogModel;
use App\Domain\Resource\Traits\HasFactory;

/**
 * @property string $received_reward
 */
class LogReceiveMessageReward extends LogModel
{
    use HasFactory;

    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }
}
