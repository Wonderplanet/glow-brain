<?php

declare(strict_types=1);

namespace App\Domain\AdventBattle\Models;

use App\Domain\Resource\Log\Models\LogModel;

/**
 * @property string $reward_category
 * @property string $received_reward
 */
class LogAdventBattleReward extends LogModel
{
    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }
}
