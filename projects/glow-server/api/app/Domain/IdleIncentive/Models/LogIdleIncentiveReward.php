<?php

declare(strict_types=1);

namespace App\Domain\IdleIncentive\Models;

use App\Domain\IdleIncentive\Enums\IdleIncentiveExecMethod;
use App\Domain\Resource\Log\Models\LogModel;
use Carbon\CarbonImmutable;

/**
 * @property string $exec_method
 * @property string $idle_started_at
 * @property int $elapsed_minutes
 * @property string $received_reward
 * @property string $received_reward_at
 */
class LogIdleIncentiveReward extends LogModel
{
    public function setExecMethod(IdleIncentiveExecMethod $execMethod): void
    {
        $this->exec_method = $execMethod->value;
    }

    public function setIdleStartedAt(CarbonImmutable $idleStartedAt): void
    {
        $this->idle_started_at = $idleStartedAt->toDateTimeString();
    }

    public function setElapsedMinutes(int $elapsedMinutes): void
    {
        $this->elapsed_minutes = $elapsedMinutes;
    }

    /**
     * @param array<mixed> $receivedReward
     */
    public function setReceivedReward(array $receivedReward): void
    {
        $this->received_reward = json_encode($receivedReward);
    }

    public function setReceivedRewardAt(CarbonImmutable $receivedRewardAt): void
    {
        $this->received_reward_at = $receivedRewardAt->toDateTimeString();
    }
}
