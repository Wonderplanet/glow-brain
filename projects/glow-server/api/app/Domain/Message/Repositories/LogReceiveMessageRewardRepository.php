<?php

declare(strict_types=1);

namespace App\Domain\Message\Repositories;

use App\Domain\Message\Models\LogReceiveMessageReward;
use App\Domain\Resource\Entities\Rewards\MessageReward;
use App\Domain\Resource\Log\Repositories\LogModelRepository;
use Illuminate\Support\Collection;

class LogReceiveMessageRewardRepository extends LogModelRepository
{
    protected string $modelClass = LogReceiveMessageReward::class;

    /**
     * @param string $usrUserId
     * @param Collection<MessageReward> $rewards
     * @return LogReceiveMessageReward
     */
    public function createByRewards(
        string $usrUserId,
        Collection $rewards,
    ): LogReceiveMessageReward {
        $model = new LogReceiveMessageReward();
        $model->setUsrUserId($usrUserId);

        $receivedRewards = [];
        foreach ($rewards as $reward) {
            $receivedRewards[] = $reward->formatToLogWithTrigger();
        }

        $model->setReceivedReward($receivedRewards);

        $this->addModel($model);

        return $model;
    }
}
